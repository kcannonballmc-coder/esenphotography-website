# Makes web-sized copies of the original photos for the static site:
#   site/photos/lg/<name>.jpg  (1600 px on the long edge)
#   site/photos/sm/<name>.jpg  (800 px on the long edge)
# and writes site/data/photos.json (name, category, size). Uses only Windows' built-in .NET imaging.

Add-Type -AssemblyName System.Drawing
$ErrorActionPreference = "Stop"

$repo     = Resolve-Path (Join-Path $PSScriptRoot "..")
$srcDir   = Join-Path $repo "photos"
$outDir   = Join-Path $repo "site\photos"
$dataFile = Join-Path $repo "site\data\photos.json"
$ids      = Get-Content (Join-Path $PSScriptRoot "photo-ids.json") -Raw | ConvertFrom-Json

$sizes = @{ lg = 1600; sm = 800 }
foreach ($k in $sizes.Keys) { New-Item -ItemType Directory -Force (Join-Path $outDir $k) | Out-Null }
New-Item -ItemType Directory -Force (Split-Path $dataFile) | Out-Null

$jpeg = [System.Drawing.Imaging.ImageCodecInfo]::GetImageEncoders() | Where-Object { $_.MimeType -eq "image/jpeg" }
function Save-Jpeg($bitmap, $path, $quality) {
	$params = New-Object System.Drawing.Imaging.EncoderParameters 1
	$params.Param[0] = New-Object System.Drawing.Imaging.EncoderParameter ([System.Drawing.Imaging.Encoder]::Quality), ([long]$quality)
	$bitmap.Save($path, $jpeg, $params)
	$params.Dispose()
}

# Rotate according to the EXIF orientation tag so photos display upright.
function Fix-Orientation($img) {
	if ($img.PropertyIdList -notcontains 0x0112) { return }
	$o = [BitConverter]::ToUInt16($img.GetPropertyItem(0x0112).Value, 0)
	$flip = switch ($o) {
		2 { "RotateNoneFlipX" } 3 { "Rotate180FlipNone" } 4 { "Rotate180FlipX" } 5 { "Rotate90FlipX" }
		6 { "Rotate90FlipNone" } 7 { "Rotate270FlipX" } 8 { "Rotate270FlipNone" } default { $null }
	}
	if ($flip) { $img.RotateFlip([System.Drawing.RotateFlipType]::$flip) }
}

$photos = @()
foreach ($entry in $ids.PSObject.Properties) {
	$src = Get-ChildItem $srcDir -Recurse -File -Filter $entry.Name | Select-Object -First 1
	$cat = $entry.Value.category
	$name = "esen-$cat-$($src.BaseName.Substring(0, 8).ToLower())"

	$img = [System.Drawing.Image]::FromFile($src.FullName)
	try {
		Fix-Orientation $img
		foreach ($k in $sizes.Keys) {
			$max = $sizes[$k]
			$scale = [Math]::Min(1.0, $max / [Math]::Max($img.Width, $img.Height))
			$w = [int][Math]::Round($img.Width * $scale); $h = [int][Math]::Round($img.Height * $scale)
			$bmp = New-Object System.Drawing.Bitmap $w, $h
			$g = [System.Drawing.Graphics]::FromImage($bmp)
			$g.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
			$g.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
			$g.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
			$g.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
			$attrs = New-Object System.Drawing.Imaging.ImageAttributes
			$attrs.SetWrapMode([System.Drawing.Drawing2D.WrapMode]::TileFlipXY)  # avoids light edges
			$g.DrawImage($img, (New-Object System.Drawing.Rectangle 0, 0, $w, $h), 0, 0, $img.Width, $img.Height, [System.Drawing.GraphicsUnit]::Pixel, $attrs)
			Save-Jpeg $bmp (Join-Path $outDir "$k\$name.jpg") $(if ($k -eq "lg") { 80 } else { 76 })
			$g.Dispose(); $bmp.Dispose(); $attrs.Dispose()
			if ($k -eq "lg") { $lgW = $w; $lgH = $h }
		}
	} finally { $img.Dispose() }

	$photos += [ordered]@{ key = $src.BaseName.Substring(0, 5); name = $name; category = $cat; width = $lgW; height = $lgH; order = $entry.Value.id }
	Write-Output ("{0,-30} {1}x{2}" -f $name, $lgW, $lgH)
}

$photos | Sort-Object { $_.order } | ForEach-Object { $_.Remove("order"); $_ } | ConvertTo-Json -Depth 3 | Set-Content -Encoding utf8 $dataFile
$total = (Get-ChildItem $outDir -Recurse -File | Measure-Object Length -Sum).Sum
Write-Output ("Done: {0} photos, {1:N1} MB total" -f $photos.Count, ($total / 1MB))

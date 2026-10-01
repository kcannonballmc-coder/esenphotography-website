# Uploads the photos in ../photos to the WordPress Media Library.
# Safe to re-run: photos already listed in tools/photo-ids.json are skipped.

. "$PSScriptRoot\wp-api.ps1"
$ErrorActionPreference = "Stop"

$photoDir = Join-Path $PSScriptRoot "..\photos"
$mapFile  = Join-Path $PSScriptRoot "photo-ids.json"

# Duplicates of other photos and the logo files are not uploaded.
$skip = @("k5cNJMcdKVIyLknmJeQLQphrA.jpg", "q1Dwe6ddxZNSFxukZH5RQW6U.jpg", "gvH9cvtP4k4gcNGHiLzW9m2BZ0.jpeg",
	"B0xkeQ8FyZ87LhSTviFYSdW8LoU.svg", "XBatYYPAtsaS9KaHqnSVdvNpbI.png")

# Photos from the draft's Home/About pages, sorted into portfolio categories.
$category = @{
	"90SvpwsTiWOgnj3AE5mpPTaIfuc.jpg" = "wedding"; "KjmEJjZDqsvzc7hw0iWx73hBTc.jpeg" = "wedding"
	"chOZL1BOZX09MPj8AwjEkIzevs.jpg" = "wedding"; "ouiv4FEzPG3DAf89jvhFrg7U04.jpeg" = "wedding"
	"5syuqHq1tlJO8H8cT0JGIYwViic.jpg" = "wedding"; "CSJ7U3TjJq6YHMexFc9Vnzusrw.jpg" = "wedding"
	"HW7Bg2Co8XJUYhydpgZ27oUPNo.jpeg" = "wedding"; "ue0AKViW6c51VIhFaWsMirpe7W0.jpg" = "wedding"
	"4bjY0SxuT8GYaTkGt8m46hTXeCE.jpg" = "wedding"; "R8PgLwV1u3SNaYPeanQMAia7e0g.jpg" = "family"
	"nC99h8aur9y7gEIr9W1BP6al2sI.jpg" = "newborn"; "geM9bAOpiqFVijrFaN6smiCBYPo.jpg" = "fashion"
	"9WbZTIlFikVIP6hq78N2oVDrzM.jpg" = "fashion"
}
$labels = @{ wedding = "Wedding"; family = "Family"; newborn = "Newborn"; fashion = "Fashion" }

$map = @{}
if (Test-Path $mapFile) {
	(Get-Content $mapFile -Raw | ConvertFrom-Json).PSObject.Properties | ForEach-Object { $map[$_.Name] = $_.Value }
}

$files = Get-ChildItem $photoDir -Recurse -File | Where-Object { $skip -notcontains $_.Name } | Sort-Object FullName
$n = 0
foreach ($file in $files) {
	$n++
	if ($map.ContainsKey($file.Name)) { continue }

	$cat = if ($category.ContainsKey($file.Name)) { $category[$file.Name] } else { $file.Directory.Name }
	$mime = if ($file.Extension -match "png") { "image/png" } else { "image/jpeg" }
	$uploadName = "esen-$cat-$($file.BaseName.Substring(0, 8).ToLower())$($file.Extension.ToLower())"

	$media = Invoke-RestMethod -Method POST -Uri "$($WPConfig.WP_URL)/wp-json/wp/v2/media" -Headers ($WPHeaders + @{
		"Content-Disposition" = "attachment; filename=`"$uploadName`""
	}) -ContentType $mime -InFile $file.FullName -TimeoutSec 600

	$alt = "$($labels[$cat]) photography by Esen Photography, Den Bosch"
	Invoke-WP POST "media/$($media.id)" @{ alt_text = $alt; title = "$($labels[$cat]) $n" } | Out-Null

	$map[$file.Name] = [ordered]@{ id = $media.id; category = $cat; url = $media.source_url }
	$map | ConvertTo-Json -Depth 4 | Set-Content -Encoding utf8 $mapFile
	Write-Output ("{0,2}/{1} {2,-8} id {3}  {4}" -f $n, $files.Count, $cat, $media.id, $uploadName)
}
Write-Output "Done. $($map.Count) photos in the media library."

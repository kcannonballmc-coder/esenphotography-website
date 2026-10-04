# Sets a descriptive alt text on each uploaded photo (keyed by the start of the original file name).
# Needs tools/photo-ids.json. Safe to re-run.

. "$PSScriptRoot\wp-api.ps1"
$ErrorActionPreference = "Stop"

$alt = [ordered]@{
	# Couples and weddings
	"4bjY0" = "Couple holding hands on a hillside in Cappadocia at golden hour"
	"5syuq" = "Bride pinning a boutonniere on the groom's jacket, bouquet in the foreground"
	"90Svp" = "Bride and groom smiling together by a lake"
	"CSJ7U" = "Laughing couple walking hand in hand in Cappadocia at sunset"
	"HW7Bg" = "Couple embracing on a bench in front of a historic building"
	"KjmEJ" = "Close-up of a smiling bride and groom, foreheads touching"
	"chOZL" = "Groom in a tuxedo beside the bride holding a dried-flower bouquet"
	"ouiv4" = "Bride and groom standing together in a garden courtyard"
	"ue0AK" = "Couple embracing on a city street with a domed building behind them"
	"0Bx1U" = "Bride in a white dress holding a dried-flower bouquet"
	"2xpQz" = "Rustic wedding favours decorated with dried flowers"
	"5ZKN6" = "Wedding cake on a table under hanging lights"
	"6YMm7" = "Bride and groom cutting their wedding cake, in black and white"
	"9QBFV" = "Bridal bouquet of lavender and white flowers held up outdoors"
	"C6HyJ" = "Couple sitting on a bench in an autumn park"
	"DE8XN" = "Close-up of the groom's tuxedo and the bride's pastel bouquet"
	"EVgFt" = "Bride in a long veil standing by a window, in black and white"
	"I5jv7" = "Smiling bride in a white hijab and groom in a tuxedo"
	"NbXju" = "Couple seen from behind watching string lights at their wedding"
	"QPfxi" = "Bride and groom laughing together in a garden"
	"T2f9k" = "Groom in a black tuxedo and bride holding a pastel bouquet"
	"TbG5i" = "Bride holding a pastel wedding bouquet, close-up"
	"VCEfE" = "Bride and groom walking under a floral arch"
	"hPggO" = "Bride and groom standing among green trees"
	"jrhTp" = "Bride in a white hijab and groom in a tuxedo in a garden"
	"kkF59" = "Groom kneeling before the bride at night, in black and white"
	"kykmA" = "Welcome sign and lanterns at a wedding venue entrance"
	"p4llm" = "Close-up of a bride and groom holding hands"
	"q5QsL" = "Bride with a bouquet and groom in a tuxedo by the sea"
	"xWeQ3" = "Bride in an embellished gown arriving at her wedding celebration"
	"xejEn" = "Bride in a white hijab and lace gown holding her bouquet"
	"xps1l" = "Couple sitting together outdoors, the woman wearing a flower crown"
	# Family
	"R8PgL" = "Parents smiling close together with their baby"
	"LqYw4" = "Family gathered around a table set with food on a terrace"
	"OkJUv" = "Mother kneeling in a garden with her toddler"
	"skz8o" = "Family sitting together on a park bench"
	# Newborn
	"nC99h" = "Newborn baby's feet cradled in a parent's hands, in black and white"
	"0vrnC" = "Sleeping newborn baby with a knitted teddy bear"
	"HY582" = "Newborn baby resting on a blanket with a parent's hand nearby"
	"I8Fi1" = "Parent kissing a sleeping newborn baby"
	"I9er6" = "Sleeping newborn baby held in a parent's hands"
	"RJxl2" = "Baby's hand holding a parent's finger, in black and white"
	"aBUcJ" = "Newborn baby's foot held in a hand, in black and white"
	"dpBQf" = "Newborn baby's tiny foot peeking out of a knitted blanket"
	"eCvOy" = "Close-up of a sleeping newborn with a knitted teddy bear"
	"f6Nne" = "Newborn baby sleeping in a white outfit, seen from above"
	"gxCc1" = "Newborn baby lying down and holding a parent's finger"
	"x1Grk" = "Close-up of a newborn baby's foot in a soft blanket"
	# Fashion
	"geM9b" = "Woman in a blue abaya and white headscarf in front of a carved wooden door"
	"9WbZT" = "Woman in a white dress and teal coat seated beside marble columns"
	"6iSTW" = "Editorial portrait of a woman in a patterned headscarf by a window"
	"9d9pl" = "Woman in sunglasses and a black headscarf on the beach"
	"A5F2I" = "Woman in a pale patterned coat standing against a stone wall"
	"SjtOa" = "Two women posing in front of a rack of colourful surfboards"
	"gty7h" = "Woman in a light blue abaya standing by a stone wall"
	"isTE5" = "Two women in matching patterned jackets and sunglasses on the beach"
}

$map = Get-Content (Join-Path $PSScriptRoot "photo-ids.json") -Raw | ConvertFrom-Json
$done = 0; $missing = @()
foreach ($file in $map.PSObject.Properties) {
	$key = $alt.Keys | Where-Object { $file.Name.StartsWith($_) } | Select-Object -First 1
	if (-not $key) { $missing += $file.Name; continue }
	Invoke-WP POST "media/$($file.Value.id)" @{ alt_text = $alt[$key] } | Out-Null
	$done++
}
Write-Output "Alt text set on $done photos."
if ($missing) { Write-Output "No description for: $($missing -join ', ')" }

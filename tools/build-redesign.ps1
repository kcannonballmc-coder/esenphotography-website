# Rebuilds page content, galleries and the main menu for the Esen theme,
# and writes the photo slots into esen/inc/config.php.
# Needs tools/photo-ids.json (created by upload-photos.ps1). Safe to re-run.

. "$PSScriptRoot\wp-api.ps1"
$ErrorActionPreference = "Stop"

$map = @{}
(Get-Content (Join-Path $PSScriptRoot "photo-ids.json") -Raw | ConvertFrom-Json).PSObject.Properties | ForEach-Object { $map[$_.Name] = $_.Value }

# Look up a photo by the start of its original file name.
function Photo($prefix) {
	$key = $map.Keys | Where-Object { $_.StartsWith($prefix) } | Select-Object -First 1
	if (-not $key) { throw "Photo not found: $prefix" }
	$map[$key]
}

function P($text) { "<!-- wp:paragraph --><p>$text</p><!-- /wp:paragraph -->" }

function GalleryBlock($photos) {
	$images = foreach ($p in $photos) {
		"<!-- wp:image {`"id`":$($p.id),`"sizeSlug`":`"large`",`"linkDestination`":`"none`"} --><figure class=`"wp-block-image size-large`"><img src=`"$($p.url)`" alt=`"`" class=`"wp-image-$($p.id)`"/></figure><!-- /wp:image -->"
	}
	"<!-- wp:gallery {`"linkTo`":`"none`"} --><figure class=`"wp-block-gallery has-nested-images columns-default is-cropped`">$($images -join '')</figure><!-- /wp:gallery -->"
}

# All photos of a category, with chosen favourites first.
function CategoryPhotos($cat, [string[]]$first) {
	$lead = @($first | ForEach-Object { Photo $_ })
	$rest = @($map.Values | Where-Object { $_.category -eq $cat -and ($lead.id -notcontains $_.id) } | Sort-Object id)
	@($lead) + @($rest)
}

function Find-Page($slug, $parent) {
	Invoke-WP GET "pages?slug=$slug&status=publish,draft,private&context=edit" | Where-Object { $_.parent -eq $parent } | Select-Object -First 1
}

function Save-Page($id, [hashtable]$fields) {
	if (-not $fields.ContainsKey("status")) { $fields.status = "publish" }
	$page = if ($id) { Invoke-WP POST "pages/$id" $fields } else { Invoke-WP POST "pages" $fields }
	Write-Output ("Page: {0,-10} id {1,-6} {2}" -f $page.title.raw, $page.id, $page.link)
	$page
}

# ---------- Pages ----------
$homePage  = Find-Page "home" 0
$portfolio = Find-Page "gallery" 0
$about     = Find-Page "about" 0
$contact   = Find-Page "contact" 0

$pHome = Save-Page $homePage.id @{ title = "Home"; content = "" } | Select-Object -Last 1

$pPortfolio = Save-Page $portfolio.id @{
	title          = "Portfolio"
	content        = ""
	excerpt        = "Weddings, families, newborns and fashion: a selection of our favourite work."
	featured_media = (Photo "T2f9k").id
} | Select-Object -Last 1

$galleries = @(
	@{ old = "weddings"; slug = "wedding"; title = "Wedding"; order = 1; cover = "jrhTp"
	   excerpt = "Your day, told naturally, from the quiet details to the celebration."
	   intro = "From the first look to the last dance, we capture your wedding day as it really felt: the emotions, the people and the little details you'll want to remember forever."
	   first = @("90Svp", "KjmEJ", "CSJ7U", "p4llm", "2xpQz", "jrhTp", "HW7Bg", "9QBFV") },
	@{ old = "portraits"; slug = "family"; title = "Family"; order = 2; cover = "skz8o"
	   excerpt = "Relaxed sessions for the people who matter most."
	   intro = "Family sessions are relaxed and playful. No stiff poses, just time together, with the laughter, cuddles and in-between moments that make your family yours."
	   first = @("OkJUv", "skz8o", "LqYw4", "R8PgL") },
	@{ old = "design"; slug = "newborn"; title = "Newborn"; order = 3; cover = "I8Fi1"
	   excerpt = "Gentle, calm portraits of the very first days."
	   intro = "Those first days go by so fast. Our newborn sessions are calm and gentle, at your baby's pace, so you can treasure the tiny fingers, toes and first expressions forever."
	   first = @("0vrnC", "nC99h", "HY582", "I8Fi1", "aBUcJ") },
	@{ old = "fashion"; slug = "fashion"; title = "Fashion"; order = 4; cover = "isTE5"
	   excerpt = "Editorial portraits for brands, designers and creatives."
	   intro = "Editorial and lookbook photography for brands, designers and creatives, with a natural, elegant style that lets the clothes and the person shine."
	   first = @("9WbZT", "geM9b", "SjtOa", "A5F2I", "isTE5") }
)

$galleryPages = @{}
foreach ($g in $galleries) {
	$existing = Find-Page $g.slug $pPortfolio.id
	if (-not $existing) { $existing = Find-Page $g.old $pPortfolio.id }
	$photos = CategoryPhotos $g.slug $g.first
	$galleryPages[$g.slug] = Save-Page $existing.id @{
		title          = $g.title
		slug           = $g.slug
		parent         = $pPortfolio.id
		menu_order     = $g.order
		excerpt        = $g.excerpt
		featured_media = (Photo $g.cover).id
		content        = (P $g.intro) + "`n`n" + (GalleryBlock $photos)
		status         = "publish"
	} | Select-Object -Last 1
}

$aboutText = @(
	P "We're Esen &amp; Mouchammed, the husband-and-wife team behind Esen Photography. Our story started over ten years ago in our university's photography club, where we met and bonded over our love for taking pictures. What began as a shared hobby turned into a life together, both as partners and as photographers."
	P "We decided to make photography our job. From our early, shaky photos to the clear, meaningful ones we take now, we've grown together, learning and having fun along the way. We've built a business that's about more than pictures: it's about connecting with people and saving special moments."
	P "As a team, we bring creativity and care to every shoot. Whether we're making a newborn smile, capturing a bride's big day or finding the best light for a fashion shot, we put our all into every photo. We've learned that the best pictures are the ones you can feel."
	P "We'd love to share our passion with you and help tell your story through our cameras."
) -join "`n`n"
$pAbout = Save-Page $about.id @{ title = "About"; content = $aboutText; featured_media = (Photo "4bjY0").id } | Select-Object -Last 1

$contactText = (P "Have a date in mind, or a question about a session? Send us a message using the form below and we'll get back to you as soon as possible.")
$pContact = Save-Page $contact.id @{ title = "Contact"; content = $contactText } | Select-Object -Last 1

# The old Services page isn't part of the new design: hide it (it can be restored from Drafts).
$services = Find-Page "services" 0
if ($services -and $services.status -ne "draft") { Invoke-WP POST "pages/$($services.id)" @{ status = "draft" } | Out-Null; Write-Output "Services page moved to drafts" }

Invoke-WP POST "settings" @{ show_on_front = "page"; page_on_front = $pHome.id } | Out-Null

# ---------- Main menu ----------
$menu = Invoke-WP GET "menus?per_page=100" | Where-Object { $_.name -eq "Main Menu" } | Select-Object -First 1
foreach ($item in (Invoke-WP GET "menu-items?menus=$($menu.id)&per_page=100")) { Invoke-WP DELETE "menu-items/$($item.id)?force=true" | Out-Null }
function Add-MenuItem($page, $order, $title, $parentItem = 0) {
	(Invoke-WP POST "menu-items" @{
		title = $title; type = "post_type"; object = "page"; object_id = $page.id
		menus = $menu.id; parent = $parentItem; menu_order = $order; status = "publish"
	}).id
}
Add-MenuItem $pHome 1 "Home" | Out-Null
$portfolioItem = Add-MenuItem $pPortfolio 2 "Portfolio"
$o = 3
foreach ($g in $galleries) { Add-MenuItem $galleryPages[$g.slug] $o $g.title $portfolioItem | Out-Null; $o++ }
Add-MenuItem $pAbout $o "About us" | Out-Null
Add-MenuItem $pContact ($o + 1) "Contact" | Out-Null
Invoke-WP POST "menus/$($menu.id)" @{ locations = @("primary") } | Out-Null
Write-Output "Main menu rebuilt"

# ---------- Theme photo slots ----------
$slots = [ordered]@{
	hero = "CSJ7U"; menu = "geM9b"; intro_a = "ue0AK"; intro_b = "5syuq"
	wedding = "KjmEJ"; family = "OkJUv"; newborn = "0vrnC"; fashion = "9WbZT"
	cta = "HW7Bg"; about = "R8PgL"; about_2 = "xps1l"; contact = "xejEn"
}
$strip = @("90Svp", "HY582", "SjtOa", "p4llm", "9QBFV", "skz8o", "jrhTp", "A5F2I", "eCvOy")
$insta = @("aBUcJ", "kkF59", "isTE5", "4bjY0", "I5jv7", "I8Fi1")

$lines = foreach ($k in $slots.Keys) { "`t`t{0}=> {1}," -f ("'" + $k + "'").PadRight(12), (Photo $slots[$k]).id }
$stripIds = ($strip | ForEach-Object { (Photo $_).id }) -join ", "
$instaIds = ($insta | ForEach-Object { (Photo $_).id }) -join ", "

$configFile = Join-Path $PSScriptRoot "..\esen\inc\config.php"
$config = [IO.File]::ReadAllText($configFile)
$block = "`t'photos'    => array(`n" + ($lines -join "`n") + "`n`t`t'strip'     => array( $stripIds ),`n`t`t'instagram' => array( $instaIds ),`n`t),"
$config = [regex]::Replace($config, "(?s)\t'photos'\s*=> array\(.*?\n\t\),", [Text.RegularExpressions.MatchEvaluator] { param($m) $block })
[IO.File]::WriteAllText($configFile, $config, (New-Object Text.UTF8Encoding $false))
Write-Output "Photo slots written to esen/inc/config.php"

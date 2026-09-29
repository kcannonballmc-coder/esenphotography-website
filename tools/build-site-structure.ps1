# One-time build of the site structure: pages, contact form, main menu, static homepage.
# Safe to re-run: existing pages (matched by slug) are updated instead of duplicated.

. "$PSScriptRoot\wp-api.ps1"
$ErrorActionPreference = "Stop"

# ---------- Contact form (Contact Form 7) ----------
# CF7 does not allow creating or editing forms over the API, so we use the existing "Contact Form 1" (id 7).
# The intended fields are in tools/contact-form.txt, to paste in WP Admin > Contact > Contact Form 1.
$form = Invoke-WP GET "/wp-json/contact-form-7/v1/contact-forms/7"
$formShortcode = "[contact-form-7 id=`"$($form.id)`" title=`"$($form.title)`"]"
Write-Output "Contact form: $($form.title) (id $($form.id))"

# ---------- Page content (Gutenberg blocks) ----------
function Btn($text, $url) {
	"<!-- wp:button --><div class=`"wp-block-button`"><a class=`"wp-block-button__link wp-element-button`" href=`"$url`">$text</a></div><!-- /wp:button -->"
}
function Buttons([string[]]$items) {
	"<!-- wp:buttons --><div class=`"wp-block-buttons`">$($items -join '')</div><!-- /wp:buttons -->"
}
function Hd($level, $text) { "<!-- wp:heading {`"level`":$level} --><h$level class=`"wp-block-heading`">$text</h$level><!-- /wp:heading -->" }
function P($text) { "<!-- wp:paragraph --><p>$text</p><!-- /wp:paragraph -->" }
function List([string[]]$items) {
	$li = ($items | ForEach-Object { "<!-- wp:list-item --><li>$_</li><!-- /wp:list-item -->" }) -join ''
	"<!-- wp:list --><ul class=`"wp-block-list`">$li</ul><!-- /wp:list -->"
}
function Columns([string[]]$cols) {
	$c = ($cols | ForEach-Object { "<!-- wp:column --><div class=`"wp-block-column`">$_</div><!-- /wp:column -->" }) -join ''
	"<!-- wp:columns --><div class=`"wp-block-columns`">$c</div><!-- /wp:columns -->"
}
function Spacer { "<!-- wp:spacer {`"height`":`"48px`"} --><div style=`"height:48px`" aria-hidden=`"true`" class=`"wp-block-spacer`"></div><!-- /wp:spacer -->" }
function Html([string[]]$blocks) { $blocks -join "`n`n" }

$homeHtml = Html @(
	Hd 1 "Photography &amp; Design by Esen"
	P "Timeless photographs and thoughtful design for the moments and brands that matter to you."
	Buttons @((Btn "View Portfolio" "/gallery/"), (Btn "Book a Session" "/contact/"))
	Spacer
	Hd 2 "What We Do"
	Columns @(
		((Hd 3 "Weddings") + (P "Natural, story-driven coverage of your day, from the quiet details to the celebration.") + (P "<a href=`"/gallery/weddings/`">See weddings &rarr;</a>")),
		((Hd 3 "Portraits") + (P "Relaxed portrait sessions for individuals, couples, families and professionals.") + (P "<a href=`"/gallery/portraits/`">See portraits &rarr;</a>")),
		((Hd 3 "Design") + (P "Logos, branding and print design that give your business a clear, consistent look.") + (P "<a href=`"/gallery/design/`">See design work &rarr;</a>"))
	)
	Spacer
	Hd 2 "Let's Work Together"
	P "Tell us about your plans and we'll get back to you with availability and options."
	Buttons @((Btn "Get in Touch" "/contact/"))
)

$gallery = Html @(
	P "A selection of recent work. Choose a category to explore."
	Columns @(
		((Hd 3 "<a href=`"/gallery/weddings/`">Weddings</a>") + (P "Wedding days, engagements and celebrations.")),
		((Hd 3 "<a href=`"/gallery/portraits/`">Portraits</a>") + (P "Individuals, couples, families and headshots.")),
		((Hd 3 "<a href=`"/gallery/design/`">Design</a>") + (P "Branding, logos and print projects."))
	)
)

function GalleryPage($intro) {
	Html @(
		P $intro
		P "<em>New gallery coming soon.</em>"
		Buttons @((Btn "Book a Session" "/contact/"))
	)
}

$services = Html @(
	P "Every project is tailored to you. Here is an overview of what we offer. Get in touch for details and pricing."
	Spacer
	Hd 2 "Wedding Photography"
	P "Full or partial day coverage, capturing the atmosphere, the people and the details of your wedding."
	List @("Consultation before the day", "Edited high-resolution images", "Private online gallery")
	Buttons @((Btn "Enquire About Weddings" "/contact/"))
	Spacer
	Hd 2 "Portrait Photography"
	P "Studio or on-location sessions for individuals, couples, families and professional headshots."
	List @("Session planning and location advice", "Edited high-resolution images", "Private online gallery")
	Buttons @((Btn "Book a Portrait Session" "/contact/"))
	Spacer
	Hd 2 "Graphic Design"
	P "Visual identity and print design for businesses and events."
	List @("Logo and brand identity", "Social media graphics", "Print: cards, flyers, invitations")
	Buttons @((Btn "Start a Design Project" "/contact/"))
)

$about = Html @(
	Hd 2 "Hello, and welcome"
	P "Esen Photography &amp; Design brings together photography and graphic design under one roof, so your images and your brand work together."
	P "Our approach is simple: listen first, keep things relaxed, and deliver work you'll be proud to share for years to come."
	Spacer
	Hd 2 "Let's Create Something Together"
	Buttons @((Btn "Get in Touch" "/contact/"))
)

$contact = Html @(
	P "Have a date in mind or a project to discuss? Send us a message and we'll reply as soon as possible."
	"<!-- wp:shortcode -->$formShortcode<!-- /wp:shortcode -->"
)

# ---------- Create / update pages ----------
function Save-Page($title, $slug, $content, $parent = 0, $order = 0) {
	$existing = Invoke-WP GET "pages?slug=$slug&status=publish,draft,private&context=edit" | Where-Object { $_.parent -eq $parent } | Select-Object -First 1
	$body = @{ title = $title; slug = $slug; content = $content; status = "publish"; parent = $parent; menu_order = $order }
	$page = if ($existing) { Invoke-WP POST "pages/$($existing.id)" $body } else { Invoke-WP POST "pages" $body }
	Write-Host ("Page: {0,-10} id {1,-6} {2}" -f $title, $page.id, $page.link)
	$page
}

$pHome      = Save-Page "Home"      "home"      $homeHtml     0 1          | Select-Object -Last 1
$pGallery   = Save-Page "Portfolio" "gallery"   $gallery  0 2          | Select-Object -Last 1
$pWeddings  = Save-Page "Weddings"  "weddings"  (GalleryPage "Wedding days, engagements and celebrations, captured naturally.") $pGallery.id 1 | Select-Object -Last 1
$pPortraits = Save-Page "Portraits" "portraits" (GalleryPage "Portrait sessions for individuals, couples, families and professionals.") $pGallery.id 2 | Select-Object -Last 1
$pDesign    = Save-Page "Design"    "design"    (GalleryPage "Branding, logos and print design projects.") $pGallery.id 3 | Select-Object -Last 1
$pServices  = Save-Page "Services"  "services"  $services 0 3          | Select-Object -Last 1
$pAbout     = Save-Page "About"     "about"     $about    0 4          | Select-Object -Last 1
$pContact   = Save-Page "Contact"   "contact"   $contact  0 5          | Select-Object -Last 1

# ---------- Static homepage ----------
Invoke-WP POST "settings" @{ show_on_front = "page"; page_on_front = $pHome.id } | Out-Null
Write-Output "Homepage set to page $($pHome.id)"

# ---------- Main menu ----------
$menuName = "Main Menu"
$menu = Invoke-WP GET "menus?per_page=100" | Where-Object { $_.name -eq $menuName } | Select-Object -First 1
if ($menu) {
	foreach ($item in (Invoke-WP GET "menu-items?menus=$($menu.id)&per_page=100")) { Invoke-WP DELETE "menu-items/$($item.id)?force=true" | Out-Null }
} else {
	$menu = Invoke-WP POST "menus" @{ name = $menuName }
}

function Add-MenuItem($page, $order, $parentItem = 0) {
	$item = Invoke-WP POST "menu-items" @{
		title = $page.title.raw; type = "post_type"; object = "page"; object_id = $page.id
		menus = $menu.id; parent = $parentItem; menu_order = $order; status = "publish"
	}
	$item.id
}
Add-MenuItem $pHome 1 | Out-Null
$galleryItem = Add-MenuItem $pGallery 2
Add-MenuItem $pWeddings 3 $galleryItem | Out-Null
Add-MenuItem $pPortraits 4 $galleryItem | Out-Null
Add-MenuItem $pDesign 5 $galleryItem | Out-Null
Add-MenuItem $pServices 6 | Out-Null
Add-MenuItem $pAbout 7 | Out-Null
Add-MenuItem $pContact 8 | Out-Null

Invoke-WP POST "menus/$($menu.id)" @{ locations = @("primary") } | Out-Null
Write-Output "Menu '$menuName' (id $($menu.id)) assigned to Primary location"

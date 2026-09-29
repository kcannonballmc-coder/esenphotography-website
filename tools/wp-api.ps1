# Helper for calling the WordPress REST API of esenphotography.com.
# Reads WP_URL / WP_USER / WP_APP_PASSWORD from the repo's .env file (never committed).
# Usage:  . "$PSScriptRoot\wp-api.ps1";  Invoke-WP GET "pages"

$script:WPConfig = @{}
Get-Content (Join-Path $PSScriptRoot "..\.env") |
	Where-Object { $_ -match '^\s*([A-Z_]+)\s*=(.*)$' } |
	ForEach-Object { $script:WPConfig[$Matches[1]] = $Matches[2].Trim() }

$script:WPHeaders = @{
	Authorization = "Basic " + [Convert]::ToBase64String(
		[Text.Encoding]::UTF8.GetBytes("$($WPConfig.WP_USER):$($WPConfig.WP_APP_PASSWORD)"))
}

# Invoke-WP <METHOD> <route under /wp-json/wp/v2, or a full /wp-json/... path> [body hashtable]
function Invoke-WP {
	param(
		[string]$Method,
		[string]$Route,
		[hashtable]$Body
	)
	$url = if ($Route.StartsWith("/")) { "$($WPConfig.WP_URL)$Route" } else { "$($WPConfig.WP_URL)/wp-json/wp/v2/$Route" }
	$params = @{ Uri = $url; Method = $Method; Headers = $WPHeaders }
	if ($Body) {
		$params.Body = [Text.Encoding]::UTF8.GetBytes(($Body | ConvertTo-Json -Depth 10))
		$params.ContentType = "application/json; charset=utf-8"
	}
	# Assign first so JSON arrays are emitted item by item (PowerShell 5.1 otherwise returns them as one object)
	$result = Invoke-RestMethod @params
	$result
}

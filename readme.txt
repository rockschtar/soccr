=== Soccr ===
Contributors: rockschtar
Donate link: https://github.com/rockschtar/soccr
Tags: football, soccer, fussball, bundesliga, openligadb
Requires at least: 7.0
Tested up to: 7.1
Requires PHP: 8.4
Stable tag: develop
License: GPL-3.0-or-later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Display football match results, standings, and upcoming matches from OpenLigaDB as Gutenberg blocks.

== Description ==

**Soccr** integrates live football data from [OpenLigaDB](https://www.openligadb.de) into the WordPress block editor. The plugin provides three fully configurable Gutenberg blocks for displaying match results, league standings, and team-specific match information — all server-side rendered and cached for performance.

= Blocks =

**Standings**
Displays a full league table for a selected league and season. The table shows position, team name, matches played, wins, draws, losses, goals, goal difference, and points. The league and season can be selected directly in the block inspector.

**Matchday**
Displays all matches for a specific matchday. Automatically shows the current matchday by default. Includes optional pagination to browse through all matchdays of a season. Matches are grouped by date and show kickoff times for upcoming games and final scores for finished ones.

**Team Match**
Displays a match for a specific team. Three display modes are available: current match (next upcoming or last finished), next match, or last match. Optionally shows team crests. The team can be selected from a list of all teams in the chosen league.

= Supported Leagues =

By default, the following leagues and competitions from OpenLigaDB are available:

* 1\. Bundesliga (bl1)
* 2\. Bundesliga (bl2)
* 3\. Liga (bl3)
* 1\. Frauen Bundesliga (fbl1)
* 2\. Frauen Bundesliga (fbl2)
* UEFA Champions League (ucl)
* UEFA Europa League (uel)
* UEFA Conference League (uecl)
* DFB-Pokal (dfb)

Additional leagues can be added via the `soccr_league_shortcuts` filter — see the *For Developers* section for an example.

= Features =

* Three ready-to-use Gutenberg blocks
* Server-side rendering — no JavaScript required on the frontend
* Built-in caching (1 hour for match and standings data, up to 24 hours for teams, leagues, and crests)
* Alignment support (left, center, right, wide, full) for all blocks
* Optional custom block title for each block
* Team crest display with built-in image proxy and caching
* Pagination for matchday browsing

= Data Attribution =

All data is provided by **OpenLigaDB** under the [Open Database License (ODbL) v1.0](https://opendatacommons.org/licenses/odbl/). Each block automatically displays the required attribution notice.

By using this plugin, your WordPress site will make requests to `https://api.openligadb.de`. No personal user data is transmitted to OpenLigaDB.

= For Developers =

Available filters:

* `soccr_league_shortcuts` — Customize which leagues are available in the block inspector (default: `['bl1', 'bl2', 'bl3', 'fbl1', 'fbl2', 'ucl', 'uel', 'uecl', 'dfb']`)
* `soccr_team_match_html` — Modify the Team Match block HTML output
* `soccr_group_matches_html` — Modify the Matchday block HTML output
* `soccr_group_matches_headline` — Modify the Matchday block headline text
* `soccr_team_icon_url` — Modify the team crest URL before it is proxied
* `soccr_league_season_display` — Modify how the season is displayed alongside the league name (default: `2025/2026`)

Example — adding leagues via `soccr_league_shortcuts`. Use any shortcut available on OpenLigaDB:

`add_filter('soccr_league_shortcuts', static function (array $shortcuts): array {
    $shortcuts[] = 'uefaeuro2024'; // UEFA Euro 2024
    $shortcuts[] = 'wm2022';       // FIFA World Cup 2022
    return $shortcuts;
});`

Available actions:

* `soccr_exception` — Triggered on API or rendering errors; use for custom error logging

== Installation ==

1. Install the plugin via *Plugins → Add New* in your WordPress admin and activate it.
2. Add any of the three Soccr blocks via the block editor. The blocks are listed under the **Soccr** category in the block inserter.
3. Select the desired league and season in the block inspector on the right-hand side.

== Frequently Asked Questions ==

= Which leagues are supported? =

By default, the German men's and women's Bundesliga divisions, the 3. Liga, the UEFA club competitions, and the DFB-Pokal are available — see the description for the full list. Any other league available on OpenLigaDB can be added using the `soccr_league_shortcuts` filter.

= Is an API key required? =

No. OpenLigaDB is a free, open service and does not require registration or an API key.

= Why does the block show outdated data? =

The plugin caches API responses as transients. Match and standings data is cached for 1 hour. If you need to refresh data immediately, clear the cached data with `wp transient delete --all` on the command line. If your site uses a persistent object cache, flush it with `wp cache flush` instead.

= Can I add custom styles? =

Yes. All blocks use BEM-style CSS classes prefixed with `wp-block-soccr-` (e.g., `.wp-block-soccr-standings`, `.wp-block-soccr-team-match`, `.wp-block-soccr-group-matches`). You can target these classes in your theme's stylesheet.

= Are team crests displayed? =

The Team Match block optionally displays team crests sourced from OpenLigaDB and Wikimedia Commons. The crest display can be toggled in the block inspector. Images are proxied through WordPress to avoid mixed-content issues and cached for 24 hours.

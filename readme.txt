=== Soccr Reloaded ===
Contributors: rockschtar
Donate link: https://github.com/rockschtar/soccr-reloaded
Tags: football, soccer, bundesliga, openligadb, gutenberg
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.4
Stable tag: 0.1.0
License: MIT
License URI: https://opensource.org/licenses/MIT

Display football match results, standings, and upcoming matches from OpenLigaDB as Gutenberg blocks.

== Description ==

**Soccr Reloaded** integrates live football data from [OpenLigaDB](https://www.openligadb.de) into the WordPress block editor. The plugin provides three fully configurable Gutenberg blocks for displaying match results, league standings, and team-specific match information — all server-side rendered and cached for performance.

= Blocks =

**Tabelle (Standings)**
Displays a full league table for a selected league and season. The table shows position, team name, matches played, wins, draws, losses, goals, goal difference, and points. The league and season can be selected directly in the block inspector.

**Spieltag (Group Matches)**
Displays all matches for a specific matchday. Automatically shows the current matchday by default. Includes optional pagination to browse through all matchdays of a season. Matches are grouped by date and show kickoff times for upcoming games and final scores for finished ones.

**Team-Spiel (Team Match)**
Displays a match for a specific team. Three display modes are available: current match (next upcoming or last finished), next match, or last match. Optionally shows team crests/icons. The team can be selected from a list of all teams in the chosen league.

= Supported Leagues =

By default, the following German leagues from OpenLigaDB are available:

* 1. Bundesliga (bl1)
* 2. Bundesliga (bl2)
* 3. Liga (bl3)

Additional leagues can be added via the `soccr_allowed_league_shortcuts` filter.

= Features =

* Three ready-to-use Gutenberg blocks
* Server-side rendering — no JavaScript required on the frontend
* Configurable caching (1 hour for matches and standings, 12 hours for matchday data, 24 hours for teams and leagues)
* Alignment support (left, center, right, wide, full) for all blocks
* Optional custom block title for each block
* Team crest display with built-in image proxy and caching
* Pagination for matchday browsing

= Data Attribution =

All data is provided by **OpenLigaDB** under the [Open Database License (ODbL) v1.0](https://opendatacommons.org/licenses/odbl/). Each block automatically displays the required attribution notice.

By using this plugin, your WordPress site will make requests to `https://api.openligadb.de`. No personal user data is transmitted to OpenLigaDB.

= For Developers =

Available filters:

* `soccr_allowed_league_shortcuts` — Customize which leagues are available in the block inspector (default: `['bl1', 'bl2', 'bl3']`)
* `soccr_team_match_html` — Modify the Team Match block HTML output
* `soccr_group_matches_html` — Modify the Group Matches block HTML output
* `soccr_group_matchtes_headline` — Modify the Group Matches headline text

Available actions:

* `soccr_exception` — Triggered on API or rendering errors; use for custom error logging

== Installation ==

1. Upload the `soccr-reloaded` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the *Plugins* menu in WordPress.
3. Add any of the three Soccr blocks via the block editor. The blocks are listed under the **Soccr** category in the block inserter.
4. Select the desired league and season in the block inspector on the right-hand side.

== Frequently Asked Questions ==

= Which leagues are supported? =

By default, the 1. Bundesliga, 2. Bundesliga, and 3. Liga from OpenLigaDB are available. You can add other leagues available on OpenLigaDB using the `soccr_allowed_league_shortcuts` filter.

= Is an API key required? =

No. OpenLigaDB is a free, open service and does not require registration or an API key.

= Why does the block show outdated data? =

The plugin caches API responses in the WordPress object cache. Match and standings data is cached for 1 hour. If you need to refresh data immediately, you can flush the object cache via a caching plugin or `wp cache flush` on the command line.

= Can I add custom styles? =

Yes. All blocks use BEM-style CSS classes prefixed with `wp-block-soccr-` (e.g., `.wp-block-soccr-standings`, `.wp-block-soccr-team-match`, `.wp-block-soccr-group-matches`). You can target these classes in your theme's stylesheet.

= Are team crests displayed? =

The Team Match block optionally displays team crests sourced from OpenLigaDB and Wikimedia Commons. The crest display can be toggled in the block inspector. Images are proxied through WordPress to avoid mixed-content issues and cached for 24 hours.

== Screenshots ==

1. Standings block showing the 2. Bundesliga table.
2. Group Matches block showing a matchday with pagination.
3. Team Match block showing the next match for a selected team.
4. Block inspector panel for the Standings block.

== Changelog ==

= 2.0.0 =
* Complete rewrite as Gutenberg block plugin
* Three server-side rendered blocks: Standings, Group Matches, Team Match
* Team crest support with image proxy
* Matchday pagination
* Caching layer via WordPress object cache

== Upgrade Notice ==

= 2.0.0 =
Complete rewrite — requires PHP 8.4 and WordPress 6.0. The old shortcode-based blocks are replaced by native Gutenberg blocks.

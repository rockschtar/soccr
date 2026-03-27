import { useEffect, useState } from '@wordpress/element';
import { ComboboxControl } from '@wordpress/components';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const LeagueSelectControl = (props) => {

  const [leagues, setLeagues] = useState([]);
  const [leagueShortcutSeason, setLeagueShortcutSeason] = useState(
    props.leagueShortcut ? props.leagueShortcut + '###' + props.leagueSeason : null
  );

  useEffect(() => {
    let cancelled = false;

    let path = '/openligadb/v1/leagues';

    if (props.leagueShortcut && props.leagueSeason) {
      path += `?includeShortcut=${props.leagueShortcut}&includeSeason=${props.leagueSeason}`;
    }

    apiFetch({ path }).then(data => {
      if (cancelled) return;

      const leagueOptions = data.map(league => ({
        value: league.leagueShortcut + '###' + league.leagueSeason,
        label: league.leagueName,
      }));

      setLeagues(leagueOptions);
    }).catch(error => {
      if (!cancelled) {
        console.error('LeagueSelectControl: fetch failed', error);
      }
    });

    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (leagues.length === 0) return;

    const valueExistsInOptions = leagues.some(l => l.value === leagueShortcutSeason);

    if (!valueExistsInOptions) {
      if (props.autoSelect) {
        onLeagueChange(leagues[0].value);
      } else {
        setLeagueShortcutSeason(null);
      }
    }
  }, [leagues]);

  const onLeagueChange = (value) => {
    if (!value) return;
    const [leagueShortcut, leagueSeason] = value.split('###');
    const selectedLeague = leagues.find(l => l.value === value);
    const leagueName = selectedLeague ? selectedLeague.label : '';
    props.onChange(leagueShortcut, leagueSeason, leagueName);
    setLeagueShortcutSeason(value);
  }

  return (
    <ComboboxControl
      label={__('Liga:', 'openligadb')}
      value={leagueShortcutSeason}
      onChange={onLeagueChange}
      options={leagues}
    />
  );
}

LeagueSelectControl.propTypes = {
  leagueShortcut: PropTypes.string,
  leagueSeason: PropTypes.number,
  onChange: PropTypes.func,
  autoSelect: PropTypes.bool,
}

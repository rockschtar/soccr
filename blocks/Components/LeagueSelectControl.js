import { useEffect, useState, useRef } from '@wordpress/element';
import { ComboboxControl } from '@wordpress/components';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const LeagueSelectControl = (props) => {

  const [leagues, setLeagues] = useState([]);
  const [leagueShortcutSeason, setLeagueShortcutSeason] = useState(props.leagueShortcut + '###' + props.leagueSeason);
  const componentMounted = useRef(true);

  useEffect(() => {
    let path = '/openligadb/v1/leagues';

    if (props.leagueShortcut && props.leagueSeason) {
      path += `?includeShortcut=${props.leagueShortcut}&includeSeason=${props.leagueSeason}`;
    }

    apiFetch({ path }).then(leagues => {

      const leagueOptions = leagues.map(league => {
        return {
          value: league.leagueShortcut + '###' + league.leagueSeason,
          label: league.leagueName,
        };
      });

      if (componentMounted.current) {
        setLeagues(leagueOptions);
      }

    });

    return () => {
      componentMounted.current = false;
    }
  }, [setLeagues]);

  const onLeagueChange = (value) => {
    if (!value) {
      return;
    }
    const league = value.split('###');
    props.onChange(league[0], league[1]);
    setLeagueShortcutSeason(value);
  }

  return (
    <ComboboxControl
      label={__('Liga:', 'openligadb')}
      value={leagueShortcutSeason}
      onChange={onLeagueChange}
      options={leagues}
    />
  )
}

LeagueSelectControl.propTypes = {
  leagueShortcut: PropTypes.string,
  leagueSeason: PropTypes.number,
  onChange: PropTypes.func,
}

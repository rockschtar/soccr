import { useEffect, useState, useRef } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const LeagueSelectControl = (props) => {

  const [leagues, setLeagues] = useState([]);
  const [leagueShortcutSeason, setLeagueShortcutSeason] = useState(props.leagueShortcut + '###' + props.leagueSeason);
  const componentMounted = useRef(true);

  useEffect(() => {

    apiFetch({ path: '/openligadb/v1/leagues' }).then(leagues => {

      let leagueOptions = leagues.map(league => {
        return {
          value: league.leagueShortcut + '###' + league.leagueSeason,
          label: league.leagueName,
        };
      });

      leagueOptions.unshift({
        value: 0,
        label: __('Select League', 'openligadb'),
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
    let league = value.split('###');
    props.onChange(league[0], league[1]);
    setLeagueShortcutSeason(value);
  }

  return (
    <SelectControl
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

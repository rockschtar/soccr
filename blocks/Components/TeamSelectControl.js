import { useEffect, useState, useRef } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import PropTypes from 'prop-types';
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';

export const TeamSelectControl = (props) => {

  const { leagueShortcut, leagueSeason } = props;
  const [teams, setTeams] = useState([]);
  const [teamId, setTeamId] = useState(props.teamId);
  const componentMounted = useRef(true);

  useEffect(() => {
    apiFetch({ path: '/openligadb/v1/teams?leagueShortcut=' + leagueShortcut + '&leagueSeason=' + leagueSeason }).then(
      teams => {

        let teamOptions = teams.map(league => {
          return {
            value: league.teamId,
            label: league.teamName,
          };
        });

        teamOptions.unshift({
          value: 0,
          label: __('Mannschaft auswählen', 'openligadb'),
        });

        if (componentMounted.current) {
          setTeams(teamOptions);
        }
      });

    return () => {
      componentMounted.current = false;
    }
  }, [leagueShortcut, leagueSeason]);

  const onTeamChange = (value) => {
    props.onChange(value);
    setTeamId(value);
  };

  return (
    <SelectControl
      label={__('Mannschaft:', 'clubfans-united')}
      value={teamId}
      onChange={onTeamChange}
      options={teams}
    />
  );
};

TeamSelectControl.propTypes = {
  leagueShortcut: PropTypes.string,
  leagueSeason: PropTypes.number,
  onChange: PropTypes.func,
};

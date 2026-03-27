import { default as ServerSideRender } from '@wordpress/server-side-render';
import { SelectControl, Panel, PanelBody } from '@wordpress/components';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { LeagueSelectControl } from '../../Components/LeagueSelectControl';
import { TeamSelectControl } from '../../Components/TeamSelectControl';

const Edit = (props) => {
    const {
        setAttributes,
        attributes,
    } = props;

    const blockProps = useBlockProps();

    const onLeagueChange = (leagueShortcut, leagueSeason) => {
        setAttributes({
            leagueShortcut,
            leagueSeason: parseInt(leagueSeason),
            teamId: 0,
        });
    };

    const onTeamChange = (teamId) => {
        setAttributes({ teamId: parseInt(teamId) });
    };

    const displayModeOptions = [
        { value: 'current', label: __('Aktuell', 'soccr') },
        { value: 'next', label: __('Nächstes Spiel', 'soccr') },
        { value: 'last', label: __('Letztes Spiel', 'soccr') },
    ];

    return (
        <div {...blockProps}>
            <InspectorControls key={'openligadb-team-match-ic'}>
                <Panel key={'openligadb-team-match-ic-panel'}>
                    <PanelBody key={'openligadb-team-match-ic-panel-body'}>
                        <LeagueSelectControl
                            key={'openligadb-team-match-league-select'}
                            leagueShortcut={attributes.leagueShortcut}
                            leagueSeason={attributes.leagueSeason}
                            onChange={onLeagueChange}
                        />
                        {attributes.leagueShortcut &&
                            <TeamSelectControl
                                key={'openligadb-team-match-team-select'}
                                leagueShortcut={attributes.leagueShortcut}
                                leagueSeason={attributes.leagueSeason}
                                teamId={attributes.teamId}
                                onChange={onTeamChange}
                            />
                        }
                        <SelectControl
                            key={'openligadb-team-match-display-mode'}
                            label={__('Anzeige:', 'soccr')}
                            value={attributes.displayMode}
                            options={displayModeOptions}
                            onChange={(displayMode) => {
                                setAttributes({ displayMode });
                            }}
                        />
                    </PanelBody>
                </Panel>
            </InspectorControls>
            <ServerSideRender
                key={'openligadb-team-match-ssr'}
                block={props.name}
                attributes={props.attributes}
            />
        </div>
    );
};

export default Edit;

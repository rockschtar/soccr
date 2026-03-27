import { default as ServerSideRender } from '@wordpress/server-side-render';
import { SelectControl, ToggleControl, Panel, PanelBody } from '@wordpress/components';
import { InspectorControls, useBlockProps, RichText } from '@wordpress/block-editor';
import { useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { LeagueSelectControl } from '../../Components/LeagueSelectControl';
import { TeamSelectControl } from '../../Components/TeamSelectControl';

const Edit = (props) => {
    const {
        setAttributes,
        attributes,
    } = props;

    const blockProps = useBlockProps();
    const [leagueName, setLeagueName] = useState('');

    const onLeagueChange = (leagueShortcut, leagueSeason, name) => {
        setLeagueName(name || '');
        const newAttributes = {
            leagueShortcut,
            leagueSeason: parseInt(leagueSeason),
            teamId: 0,
        };
        if (!attributes.title && name) {
            newAttributes.title = name;
        }
        setAttributes(newAttributes);
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
                        <ToggleControl
                            label="Titel anzeigen"
                            checked={attributes.showTitle}
                            onChange={(showTitle) => setAttributes({ showTitle })}
                        />
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

            {attributes.showTitle && (
                <RichText
                    tagName="h2"
                    value={attributes.title}
                    onChange={(title) => setAttributes({ title })}
                    placeholder={leagueName || 'Team Spiel'}
                />
            )}

            <ServerSideRender
                key={'openligadb-team-match-ssr'}
                block={props.name}
                attributes={props.attributes}
            />
        </div>
    );
};

export default Edit;

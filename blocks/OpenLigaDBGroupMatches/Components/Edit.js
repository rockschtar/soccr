import { default as ServerSideRender } from '@wordpress/server-side-render';
import { TextControl, CheckboxControl, ToggleControl, Panel, PanelBody } from '@wordpress/components';
import { InspectorControls, useBlockProps, RichText } from '@wordpress/block-editor';
import { useEffect, useState } from '@wordpress/element';
import { LeagueSelectControl } from '../../Components/LeagueSelectControl';

const Edit = (props) => {

  const {
    setAttributes,
    attributes,
    clientId,
  } = props;

  const { blockId } = attributes;

  const blockProps = useBlockProps();
  const [leagueName, setLeagueName] = useState('');

  useEffect(() => {
    if (!blockId) {
      setAttributes({
        blockId: clientId,
      });
    }

  }, [clientId]);

  return (
    <div {...blockProps}>
      <InspectorControls key={'openligadb-group-matches-ic'}>
        <Panel key={'openligadb-group-matches-ic-panel'}>
          <PanelBody key={'openligadb-group-matches-ic-panel-body'}>
            <ToggleControl
              label="Titel anzeigen"
              checked={attributes.showTitle}
              onChange={(showTitle) => setAttributes({ showTitle })}
            />
            <LeagueSelectControl
              leagueShortcut={attributes.leagueShortcut}
              leagueSeason={attributes.leagueSeason}
              onChange={(leagueShortcut, leagueSeason, name) => {
                setLeagueName(name || '');
                const newAttributes = { leagueShortcut, leagueSeason: parseInt(leagueSeason) };
                if (!attributes.title && name) {
                  newAttributes.title = name;
                }
                setAttributes(newAttributes);
              }}
            />

            <CheckboxControl key={'openligadb-attribute-league-defaultcurrentgroup'}
                             label="Aktuelle GroupOrderId anzeigen"
                             checked={attributes.defaultCurrentGroup}
                             onChange={(defaultCurrentGroup) => {
                               setAttributes({ defaultCurrentGroup });
                             }}/>

            {attributes.defaultCurrentGroup === false &&
            <TextControl key={'openligadb-attribute-league-grouporderid'} type="number" min={1}
                         label="OpenLigaDB GroupOrderId" value={attributes.groupOrderId ?? 1}
                         onChange={(groupOrderId) => {
                           setAttributes({ groupOrderId: parseInt(groupOrderId) });
                         }}/>
            }
            <CheckboxControl key={'openligadb-attribute-league-pagination'} label="Blättern anzeigen"
                             checked={attributes.pagination}
                             onChange={(pagination) => {
                               setAttributes({ pagination });
                             }}/>

          </PanelBody>
        </Panel>
      </InspectorControls>

      {attributes.showTitle && (
        <RichText
          tagName="h2"
          value={attributes.title}
          onChange={(title) => setAttributes({ title })}
          placeholder={leagueName || 'Spieltag'}
        />
      )}

      <ServerSideRender
        key={'openligadb-group-matches-ssr'}
        block={props.name}
        attributes={props.attributes}
      />
    </div>
  );

};

export default Edit;

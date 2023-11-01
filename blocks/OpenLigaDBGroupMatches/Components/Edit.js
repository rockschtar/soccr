import { default as ServerSideRender } from '@wordpress/server-side-render';
import { TextControl, CheckboxControl, Panel, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';

const Edit = (props) => {

  const {
    setAttributes,
    attributes,
    className,
    clientId,
  } = props;

  const { blockId } = attributes;

  useEffect(() => {
    if (!blockId) {
      setAttributes({
        blockId: clientId,
      });
    }

  }, [clientId]);

  return [
    <InspectorControls key={'openligadb-group-matches-ic'}>
      <Panel key={'openligadb-group-matches-ic-panel'}>
        <PanelBody key={'openligadb-group-matches-ic-panel-body'}>
          <TextControl key={'openligadb-attribute-league-shortcut'}
                       label="OpenLigaDB League Shortcut"
                       value={attributes.leagueShortcut}
                       onChange={(leagueShortcut) => {
                         setAttributes({ leagueShortcut });
                       }}/>

          <CheckboxControl key={'openligadb-attribute-league-defaultcurrentgroup'}
                           label="Aktuelle GroupOrderId anzeigen"
                           checked={attributes.defaultCurrentGroup}
                           onChange={(defaultCurrentGroup) => {
                             setAttributes({ defaultCurrentGroup });
                           }}/>

          {attributes.defaultCurrentGroup === false &&
          <>
            <TextControl key={'openligadb-attribute-league-season'} type="number"
                         label="OpenLigaDB League Season" value={attributes.leagueSeason}
                         onChange={(leagueSeason) => {
                           setAttributes({ leagueSeason });
                         }}/>


            <TextControl key={'openligadb-attribute-league-grouporderid'} type="number" min={1}
                         label="OpenLigaDB GroupOrderId" value={attributes.groupOrderId ?? 1}
                         onChange={(groupOrderId) => {
                           setAttributes({ groupOrderId: parseInt(groupOrderId) });
                         }}/>
          </>
          }
          <CheckboxControl key={'openligadb-attribute-league-pagination'} label="Blättern anzeigen"
                           checked={attributes.pagination}
                           onChange={(pagination) => {
                             setAttributes({ pagination });
                           }}/>

        </PanelBody>
      </Panel>
    </InspectorControls>,

    <ServerSideRender
      key={'openligadb-group-matches-ssr'}
      block={props.name}
      attributes={props.attributes}
    />,

  ];

};

export default Edit;

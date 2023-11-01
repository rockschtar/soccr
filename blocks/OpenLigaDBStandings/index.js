import { registerBlockType } from '@wordpress/blocks';
import { default as Edit } from './Components/Edit';
import "./style.scss"
import metadata from './block.json';

registerBlockType(metadata, {
  edit : Edit,
  save(props) {
    return null;
  },
});

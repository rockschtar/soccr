import { registerBlockType } from '@wordpress/blocks';
import { default as Edit } from './Components/Edit';
import { default as Save } from './Components/Save';
import "./style.scss"
import metadata from './block.json';

registerBlockType(metadata, {
  edit : Edit,
  save: Save,
});

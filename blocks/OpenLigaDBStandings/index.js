import { registerBlockType } from '@wordpress/blocks';
import { default as Edit } from './Components/Edit';
import { default as Save } from './Components/Save';
import { standingsIcon } from '../Components/icons';
import "./style.scss"
import metadata from './block.json';

registerBlockType(metadata, {
  icon: standingsIcon,
  edit : Edit,
  save: Save,
});

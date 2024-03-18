import { mount } from '@vue/test-utils';
import { ColorFacet } from '../../../shared/interfaces';
import ItemColorTiles from './ItemColorTiles.vue';
import $ from 'jquery';

window.$ = $;
window.SVGInjector = jest.fn;

describe('ItemColorTiles', () => {
    it('must show all color tiles', () => {
        const facet: ColorFacet = {
            id: 'Color',
            values: [
                {
                    name : 'Red',
                    colorHexCode: '#ff0000',
                    colorImageUrl : '',
                    media : { url : '' },
                    selected: false,
                    values: []
                },
                {
                    name : 'Colorful',
                    colorHexCode: null,
                    colorImageUrl: 'https://your-store.com/images/colors/colorful.png',
                    media : { url : 'https://your-store.com/images/colors/colorful.png' },
                    selected: false,
                    values: []
                },
                {
                    name : 'Unknown',
                    colorHexCode: null,
                    media : { url : '' },
                    colorImageUrl: '',
                    selected: false,
                    values: []
                }
            ]
        };

        const component = mount(ItemColorTiles, { propsData: { facet } });

        const colorTiles = component.findAll('.fl-color-tile-background');
        expect(colorTiles.length).toBe(3);
    });
});

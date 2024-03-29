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
                    name: 'Red',
                    hexValue: '#ff0000',
                    selected: false,
                    items: []
                },
                {
                    name: 'Colorful',
                    hexValue: null,
                    colorImageUrl: 'https://your-store.com/images/colors/colorful.png',
                    selected: false,
                    items: []
                },
                {
                    name: 'Unknown',
                    hexValue: null,
                    selected: false,
                    items: []
                }
            ]
        };

        const component = mount(ItemColorTiles, { propsData: { facet } });

        const colorTiles = component.findAll('.fl-color-tile-background');
        expect(colorTiles.length).toBe(3);
    });
});

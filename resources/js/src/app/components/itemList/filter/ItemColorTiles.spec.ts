import { mount } from '@vue/test-utils';
import { ColorFacet } from '../../../shared/interfaces';
import ItemColorTiles from './ItemColorTiles.vue';

describe('ItemColorTiles', () => {
    it('must show all color tiles', () => {
        const facet: ColorFacet = {
            id: 'Color',
            values: [
                {
                    name: 'Red',
                    hexValue: '#ff0000',
                    selected: false
                },
                {
                    name: 'Colorful',
                    hexValue: null,
                    colorImageUrl: 'https://your-store.com/images/colors/colorful.png',
                    selected: false
                },
                {
                    name: 'Unknown',
                    hexValue: null,
                    selected: false
                }
            ]
        };

        const component = mount(ItemColorTiles, { propsData: { facet } });

        const colorTiles = component.findAll('.fl-color-tile-background');
        expect(colorTiles.length).toBe(3);
    });
});

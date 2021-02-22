import { mount } from '@vue/test-utils';
import { ColorFacet } from '../../../interfaces';
import ItemColorTiles from './ItemColorTiles.vue';

describe('ItemColorTiles', () => {
    it('must show all color tiles', () => {
        const facet: ColorFacet = {
            id: 'Color',
            values: [
                {
                    name: 'Red',
                    hexValue: '#ff0000',
                },
                {
                    name: 'Colorful',
                    hexValue: null,
                    colorImageUrl: 'https://your-store.com/images/colors/colorful.png'
                },
                {
                    name: 'Unknown',
                    hexValue: null,
                }
            ]
        };

        const component = mount(ItemColorTiles, {propsData: {facet}});

        const actualColors = component.element.querySelectorAll('.fl-color-tile-background');
        expect(actualColors.length).toBe(3);
    });
});

import { mount } from '@vue/test-utils';
import TestComponent from './TestComponent.vue';

describe('TestComponent', () => {
    it('should contain some demo text', () => {
        const testComponent = mount(TestComponent);

        expect(testComponent.element.innerHTML).toContain('Hello World!')
        expect(testComponent.element.innerHTML).toContain('This should be red. Test: Hi Mom!')
    })
})

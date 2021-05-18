import { mount } from '@vue/test-utils';
import TestComponent from './TestComponent.vue';

describe('TestComponent', () => {
    it('should contain some demo text', () => {
        const testComponent = mount(TestComponent);

        expect(testComponent.element.innerHTML).toContain('Hello World!');
        expect(testComponent.element.innerHTML)
            .toContain('<h1>Hello World!</h1> <span class="fl-red-text">This should be red! Test: Hi Mom!. HMR is awesome!!</span>');
    });
});

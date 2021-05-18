import Vue from 'vue';

Vue.directive('render-category',
{
    bind(el)
    {
        el.onclick = event =>
        {
            event.preventDefault();

            window.open(event.target.href, '_self');
        };
    }
});

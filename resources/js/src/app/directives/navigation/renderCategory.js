Vue.directive("render-category",
{
    bind(el, binding)
    {
        el.onclick = event =>
        {
            event.preventDefault();

            window.open(event.target.href, '_self');
        };
    }
});

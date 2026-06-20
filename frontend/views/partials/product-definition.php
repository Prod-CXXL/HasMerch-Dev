{% assign item = include.product | default: page %}

{% if item.available == false %}
  <button class="buy-button coming-soon" disabled>
    COMING SOON
  </button>
{% else %}
<!--
  <button
    class="buy-button snipcart-add-item"
    data-item-id="{{ item.identifier | default: item.slug }}"
    data-item-name="{{ item.title }}"
    data-item-price="{{ item.price }}"
    data-item-url="{{ site.baseurl }}{{ item.url }}"
    data-item-image="{{ site.baseurl }}{{ item.image }}"
    data-item-description="{{ item.description }}">
    Add to cart (${{ item.price }})
  </button>
-->
 <button class="buy-button stripe-buy"
  data-name="{{ page.title }}"
  data-price="{{ page.price | times: 100 | round }}">
  Buy with Stripe (${{ page.price }})
</button>

{% endif %}

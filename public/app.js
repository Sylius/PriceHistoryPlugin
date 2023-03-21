/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

function handleProductOptionChanges() {
  $('[name*="sylius_add_to_cart[cartItem][variant]"]').on('change', () => {
    let selector = '';

    $('#sylius-product-adding-to-cart select[data-option]').each((index, element) => {
      const select = $(element);
      const option = select.find('option:selected').val();
      selector += `[data-${select.attr('data-option')}="${option}"]`;
    });

    const lowestPriceBeforeTheDiscount = $('#sylius-variants-pricing').find(selector).attr('data-product-lowest-price-before-discount')

    if (lowestPriceBeforeTheDiscount !== undefined) {
      $('#product-lowest-price-before-discount').html(lowestPriceBeforeTheDiscount);
      $('#product-lowest-price-before-discount').css({'white-space': 'nowrap', 'display': 'inline'});
    } else {
      $('#product-lowest-price-before-discount').css('display', 'none');
    }
  });
};

function handleProductVariantChanges() {
  $('[name="sylius_add_to_cart[cartItem][variant]"]').on('change', (event) => {
    const priceRow = $(event.currentTarget).parents('tr').find('.sylius-product-variant-price');
    const price = priceRow.text();

    $('#product-price').text(price);

    const lowestPriceBeforeTheDiscount = priceRow.attr('data-product-lowest-price-before-discount');

    if (lowestPriceBeforeTheDiscount !== undefined) {
      $('#product-lowest-price-before-discount').html(lowestPriceBeforeTheDiscount);
      $('#product-lowest-price-before-discount').css({'white-space': 'nowrap', 'display': 'inline'});
    } else {
      $('#product-lowest-price-before-discount').css('display', 'none');
    }
  });
};

$(document).ready(function() {
  handleProductOptionChanges();
  handleProductVariantChanges();
});

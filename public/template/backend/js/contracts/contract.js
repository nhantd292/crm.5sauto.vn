load_action('#loadProducts', '/xadmin/api/loadKovProducts/', null);
// Xử lý load tỉnh thành
$('select[name="location_city_id"]').change(function () {
    var select = 'input[name="location_district_id"]';
    var parent = $(select).parent();
    $('.select2-container', parent).select2('val', '');
    $(select).attr('data-parent', $(this).val());

    var select_x = 'input[name="location_town_id"]';
    var parent_x = $(select_x).parent();
    $('.select2-container', parent_x).select2('val', '');
    $(select_x).attr('data-parent', $(this).val());
});
$('input[name="location_district_id"]').change(function() {
    var select = 'input[name="location_town_id"]';
    var parent = $(select).parent();
    $('.select2-container', parent).select2('val', '');
    $(select).attr('data-parent', $(this).val());
});

$('select[name="unit_transport"]').change(function() {
    var unit = $(this).val();
    if(unit == '5sauto'){
        $('.unit-child').addClass('hidden')
        $('.'+unit).addClass('hidden')
    }

    var select = 'input[name="location_town_id"]';
    var parent = $(select).parent();
    $('.select2-container', parent).select2('val', '');
    $(select).attr('data-parent', $(this).val());
});

var contactPhone = $('#contactPhone').text().trim();
if (contactPhone) {
    load_action('#load_contract', '/xadmin/api/list-contract-by-phone/', {phone: contactPhone});
}

// Kiểm tra thông tin khách hàng
var contactId = $('#contactId').text().trim();
if (contactId) {
    checkContactToElement(contactId, 'element');
}

updateTotal()

function updateTotal() {
    var total_contract = 0;
    var total_contract_product = 0;
    var total_contract_vat = $('.total_contract_vat input').val() ? $('.total_contract_vat input').val() : 0;
    var fee_other = $('.fee_other input').val() ? $('.fee_other input').val() : 0;
    var total_contract_discount = $('.total_contract_discount input').val() ? $('.total_contract_discount input').val() : 0;
    var price_deposits = $('.price_deposits input').val() ? $('.price_deposits input').val() : 0;
    $.each($('.list-product-contract tr'), function (index, value) {
        var price = $(this).find('.price > input').val() ? $(this).find('.price > input').val() : 0;
        var number = $(this).find('.numbers input').val() ? $(this).find('.numbers input').val() : 0;
        total_contract_product += parseInt(unFormatNumber(price) * unFormatNumber(number));
        total_contract += parseInt(unFormatNumber(price) * unFormatNumber(number));
    });
    total_contract += parseInt(unFormatNumber(total_contract_vat));
    total_contract += parseInt(unFormatNumber(fee_other));
    total_contract -= parseInt(unFormatNumber(total_contract_discount));
    var price_total = total_contract;
    total_contract -= parseInt(unFormatNumber(price_deposits));
    $(".total_contract_vat input").val(formatNumber(total_contract_vat));
    $(".price_owed span").text(formatNumber(total_contract));
    $(".price_owed input").val(formatNumber(total_contract));
    $(".total_contract_product span").text(formatNumber(total_contract_product));
    $(".total_contract_product input").val(formatNumber(total_contract_product));

    $("input[name=price_total]").val(price_total)
}

function resetDiscounts() {
    $('.total_contract_discount input').val(0)
    $('tr.product_gif').remove()
}
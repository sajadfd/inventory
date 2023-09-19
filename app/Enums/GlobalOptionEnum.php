<?php

namespace App\Enums;

enum GlobalOptionEnum
{
    //You need to assign a default value also inside App\Services\GlobalOptionsSync
    const CurrencyValue = 'currency_value';
    const HeaderImage = 'header_image';
    const FooterImage = 'footer_image';
    const InventoryName = "inventory_name";
    const PaginatorPerPage = 'paginator_per_page';
    const IqdSaleToNearestPayablePrice = 'iqd_sale_to_nearest_payable_price';
    const WhatsappContact = "whatsapp_contact";
    const FacebookContact = "facebook_contact";
    const InstagramContact = "instagram_contact";
    const YoutubeContact = "youtube_contact";
    const TwitterContact = "twitter_contact";
    const PhoneContact = "phone_contact";
    const AddressContact = "address_contact";
    const UnifyUnpaidSalaries = 'unify_unpaid_salaries';
    const ConsiderAllMonthsAsThirtyDays = 'consider_all_months_as_thirty_days';

}



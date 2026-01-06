<?php

namespace App\Helpers;

class WhatsAppHelper
{
    /**
     * Format phone number and create WhatsApp link with icon
     * Converts local format (0812...) to international format (+62812...)
     * 
     * @param string|null $phone
     * @return string HTML link with WhatsApp icon or '-'
     */
    public static function formatPhoneWithLink($phone)
    {
        if (!$phone) {
            return '-';
        }
        
        $phone = trim($phone);
        
        // Convert to international format
        if (substr($phone, 0, 1) === '0') {
            $phone = '+62' . substr($phone, 1);
        } elseif (substr($phone, 0, 1) !== '+') {
            $phone = '+62' . $phone;
        }
        
        // Create WhatsApp URL
        $whatsappUrl = 'https://wa.me/' . str_replace('+', '', $phone);
        $displayPhone = $phone;
        
        // Return HTML link with WhatsApp icon
        return sprintf(
            '<a href="%s" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center gap-2" title="Chat on WhatsApp">
                <span>%s</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="inline-block">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.67-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.076 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421-7.403h-.004a9.87 9.87 0 00-4.869 1.171c-1.519.754-2.753 1.77-3.644 3.04C2.54 10.731 2 12.475 2 14.313c0 1.042.133 2.04.39 3.006l.52 1.994-2.144 2.144a.5.5 0 00.707.707l2.144-2.144 1.994.52c.966.256 1.964.39 3.006.39 1.838 0 3.582-.54 5.119-1.318 1.27-.891 2.286-2.125 3.04-3.644.754-1.519 1.171-3.226 1.171-4.869a9.865 9.865 0 00-1.171-4.869c-.754-1.519-1.77-2.753-3.04-3.644-1.519-.754-3.226-1.171-4.869-1.171M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0"/>
                </svg>
            </a>',
            $whatsappUrl,
            $displayPhone
        );
    }
}

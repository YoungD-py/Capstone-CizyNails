<?php

if (!function_exists('getStatusBadge')) {
    /**
     * Get HTML badge for booking status
     */
    function getStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-medium">Pending - Belum Dibayar</span>',
            'confirmed' => '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">Confirmed - Sudah Bayar</span>',
            'completed' => '<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full font-medium">Completed - Selesai</span>',
            'cancelled' => '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full font-medium">Cancelled - Dibatalkan</span>',
        ];

        return $badges[$status] ?? '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-medium">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('getPaymentStatusBadge')) {
    /**
     * Get HTML badge for payment status
     */
    function getPaymentStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-medium">Pending - Belum Dibayar</span>',
            'paid' => '<span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">Paid - Sudah Dibayar</span>',
            'unpaid' => '<span class="px-2 py-1 bg-red-100 text-red-800 text-xs rounded-full font-medium">Unpaid - Gagal/Expired</span>',
        ];

        return $badges[$status] ?? '<span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full font-medium">' . ucfirst($status) . '</span>';
    }
}

if (!function_exists('getStatusColor')) {
    /**
     * Get color class for booking status
     */
    function getStatusColor($status)
    {
        $colors = [
            'pending' => 'yellow',
            'confirmed' => 'green',
            'completed' => 'blue',
            'cancelled' => 'red',
        ];

        return $colors[$status] ?? 'gray';
    }
}

if (!function_exists('getPaymentStatusColor')) {
    /**
     * Get color class for payment status
     */
    function getPaymentStatusColor($status)
    {
        $colors = [
            'pending' => 'yellow',
            'paid' => 'green',
            'unpaid' => 'red',
        ];

        return $colors[$status] ?? 'gray';
    }
}

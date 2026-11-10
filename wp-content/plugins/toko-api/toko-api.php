<?php
/**
 * Plugin Name: Toko API (Hardcode)
 * Description: API custom dengan data hardcode untuk latihan REST API WordPress.
 * Version: 1.0.0
 * Author: Akmal
 */

// Cegah file ini diakses langsung dari browser.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * DATA HARDCODE: ditulis langsung di kode, bukan dari database.
 */
function toko_api_categories() {
    return [
        [ 'id' => 1, 'name' => 'Makanan' ],
        [ 'id' => 2, 'name' => 'Minuman' ],
        [ 'id' => 3, 'name' => 'Elektronik' ],
    ];
}

function toko_api_products() {
    return [
        [ 'id' => 1, 'name' => 'Nasi Goreng', 'price' => 25000,  'stock' => 10, 'category_id' => 1 ],
        [ 'id' => 2, 'name' => 'Mie Goreng',  'price' => 22000,  'stock' => 15, 'category_id' => 1 ],
        [ 'id' => 3, 'name' => 'Es Teh',      'price' => 5000,   'stock' => 50, 'category_id' => 2 ],
        [ 'id' => 4, 'name' => 'Keyboard',    'price' => 60000,  'stock' => 3,  'category_id' => 3 ],
        [ 'id' => 5, 'name' => 'Monitor',     'price' => 500000, 'stock' => 5,  'category_id' => 3 ],
    ];
}

/**
 * ROUTE: daftar alamat API (seperti routes/api.php di Laravel).
 */
add_action( 'rest_api_init', function () {

    // GET /wp-json/toko/v1/categories
    register_rest_route( 'toko/v1', '/categories', [
        'methods'             => 'GET',
        'callback'            => 'toko_api_get_categories',
        'permission_callback' => '__return_true', // publik, tanpa login
    ] );

    // GET dan POST /wp-json/toko/v1/products
    register_rest_route( 'toko/v1', '/products', [
        [
            // GET: daftar produk (opsional: ?category_id=1)
            'methods'             => WP_REST_Server::READABLE,
            'callback'            => 'toko_api_get_products',
            'permission_callback' => '__return_true',
            'args'                => [
                'category_id' => [
                    'required'          => false,
                    'sanitize_callback' => 'absint',
                ],
            ],
        ],
        [
            // POST: tambah produk (simulasi, tidak disimpan)
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => 'toko_api_create_product',
            'permission_callback' => '__return_true',
            'args'                => [
                'name'        => [ 'required' => true, 'type' => 'string',  'sanitize_callback' => 'sanitize_text_field' ],
                'price'       => [ 'required' => true, 'type' => 'integer', 'minimum' => 0 ],
                'stock'       => [ 'required' => true, 'type' => 'integer', 'minimum' => 0 ],
                'category_id' => [ 'required' => true, 'type' => 'integer' ],
            ],
        ],
    ] );

    // GET /wp-json/toko/v1/products/5
    register_rest_route( 'toko/v1', '/products/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'toko_api_get_product',
        'permission_callback' => '__return_true',
    ] );
} );

/**
 * CALLBACK: isi logika tiap alamat (seperti method di Controller).
 */
function toko_api_get_categories( WP_REST_Request $request ) {
    return new WP_REST_Response( [
        'success' => true,
        'data'    => toko_api_categories(),
    ], 200 );
}

function toko_api_get_products( WP_REST_Request $request ) {
    $products    = toko_api_products();
    $category_id = $request->get_param( 'category_id' );

    if ( $category_id ) {
        $products = array_values( array_filter( $products, function ( $p ) use ( $category_id ) {
            return $p['category_id'] === (int) $category_id;
        } ) );
    }

    return new WP_REST_Response( [
        'success' => true,
        'data'    => $products,
    ], 200 );
}

function toko_api_get_product( WP_REST_Request $request ) {
    $id = (int) $request['id'];

    foreach ( toko_api_products() as $product ) {
        if ( $product['id'] === $id ) {
            return new WP_REST_Response( [
                'success' => true,
                'data'    => $product,
            ], 200 );
        }
    }

    return new WP_Error(
        'toko_product_not_found',
        'Produk tidak ditemukan',
        [ 'status' => 404 ]
    );
}

function toko_api_create_product( WP_REST_Request $request ) {
    $category_id = (int) $request['category_id'];

    // Pastikan kategorinya ada di data hardcode
    $found = false;
    foreach ( toko_api_categories() as $category ) {
        if ( $category['id'] === $category_id ) {
            $found = true;
            break;
        }
    }

    if ( ! $found ) {
        return new WP_Error(
            'toko_category_not_found',
            'Kategori tidak ditemukan',
            [ 'status' => 422 ]
        );
    }

    // Data hardcode: ID baru ditentukan di kode dan produk TIDAK disimpan.
    $product = [
        'id'          => count( toko_api_products() ) + 1,
        'name'        => $request['name'],
        'price'       => (int) $request['price'],
        'stock'       => (int) $request['stock'],
        'category_id' => $category_id,
    ];

    return new WP_REST_Response( [
        'success' => true,
        'message' => 'Produk berhasil dibuat (simulasi, tidak disimpan)',
        'data'    => $product,
    ], 201 );
}
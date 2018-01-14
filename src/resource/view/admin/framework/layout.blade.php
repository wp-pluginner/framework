<div class="wrap">
    <h1>
        {{ $config->get('name') }}
    </h1>
    @yield('content')
</div>

<style type="text/css">
    /* No Need to Enqueue, Inline is Faster */
    .wp-pluginium-list-group {
        padding: 0;
        margin: 0;
        box-shadow: 0 1px 2px #ccc;
    }

    .wp-pluginium-list-group li.active a {
        background: #0073aa;
        color: #fff;
    }

    .wp-pluginium-list-group li {
        padding: 0;
        margin: 0;
        border-bottom: 1px solid #eee;
    }

    .wp-pluginium-list-group li:last-of-type {
        overflow: hidden;
        border: none;
    }

    .wp-pluginium-list-group li a,
    .wp-pluginium-list-group li h6 {
        display: block;
        padding: 0 20px;
        line-height: 35px;
        margin: 0;
        font-size: 14px;
        background-color: #f9f9f9;
    }

    .wp-pluginium-list-group li h6 {
        font-size: 15px;
    }

    @media (max-width: 782px) {

        .wp-pluginium-table-scrollable {
            overflow-x: auto;
            display: inline-block;
            width: auto;
            max-width: 100%;
        }

        .wp-pluginium-table-scrollable > * > tr > th,
        .wp-pluginium-table-scrollable > * > tr > td {
            white-space: nowrap;
            display: table-cell;
        }
    }

</style>

<div class="wrap">
    <h1>
        {{ $config->get('name') }}
    </h1>
    @yield('content')
</div>

<style type="text/css">
    /* No Need to Enqueue, Inline is Faster */
    .wp_pluginner-list-group {
        padding: 0;
        margin: 0;
        box-shadow: 0 1px 2px #ccc;
    }

    .wp_pluginner-list-group li.active a {
        background: #0073aa;
        color: #fff;
    }

    .wp_pluginner-list-group li {
        padding: 0;
        margin: 0;
        border-bottom: 1px solid #eee;
    }

    .wp_pluginner-list-group li:last-of-type {
        overflow: hidden;
        border: none;
    }

    .wp_pluginner-list-group li a,
    .wp_pluginner-list-group li h6 {
        display: block;
        padding: 0 20px;
        line-height: 35px;
        margin: 0;
        font-size: 14px;
        background-color: #f9f9f9;
    }

    .wp_pluginner-list-group li h6 {
        font-size: 15px;
    }

    @media (max-width: 782px) {

        .wp_pluginner-table-scrollable {
            overflow-x: auto;
            display: inline-block;
            width: auto;
            max-width: 100%;
        }

        .wp_pluginner-table-scrollable > * > tr > th,
        .wp_pluginner-table-scrollable > * > tr > td {
            white-space: nowrap;
            display: table-cell;
        }
    }

</style>

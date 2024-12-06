jQuery(document).ready(function($) {
    function loadCities(page = 1) {
        var search = $('#cities-search-input').val();
        var nonce = citiesAjax.nonce;

        $.ajax({
            url: citiesAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'load_cities',
                search: search,
                page: page,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    var cities = response.data.cities;
                    var total_pages = response.data.total_pages;
                    var current_page = response.data.current_page;

                    var table_body = $('#cities-table tbody');
                    table_body.empty();

                    $.each(cities, function(index, city) {
                        table_body.append('<tr><td>' + city.country + '</td><td>' + city.city + '</td><td>' + city.temp + ' °C </td></tr>');
                    });

                    var pagination = $('#cities-pagination');
                    pagination.empty();

                    if (total_pages > 1) {
                        if (current_page > 1) {
                            var prev_link = $('<a href="#" class="page-link prev">&laquo; Prev</a>');
                            prev_link.data('page', current_page - 1);
                            pagination.append(prev_link);
                        }

                        for (var i = 1; i <= total_pages; i++) {
                            var page_link = $('<a href="#" class="page-link">' + i + '</a>');
                            if (i === current_page) {
                                page_link.addClass('current');
                            }
                            page_link.data('page', i);
                            pagination.append(page_link);
                        }

                        if (current_page < total_pages) {
                            var next_link = $('<a href="#" class="page-link next">Next &raquo;</a>');
                            next_link.data('page', current_page + 1);
                            pagination.append(next_link);
                        }

                        pagination.show();
                    } else {
                        pagination.hide();
                    }
                }
            }
        });
    }

    $('#cities-search-button').on('click', function() {
        loadCities();
    });

    $(document).on('click', '.page-link', function(e) {
        e.preventDefault();
        var page = $(this).data('page');
        loadCities(page);
    });

    loadCities();
});
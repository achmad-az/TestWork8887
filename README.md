# Cities Weather Widget & Custom Post Type

This project is a WordPress solution that includes a custom post type for “Cities,” a custom taxonomy for “Countries,” and a widget to display the weather of selected cities. It also includes a custom page template for displaying a searchable table of countries, cities, and their temperatures.

## Features

- **Custom Post Type: Cities**
    - Stores information about different cities.
    - Includes meta fields for latitude and longitude.
- **Custom Taxonomy: Countries**
    - Hierarchical taxonomy linked to the Cities post type.
- **Weather Widget**
    - Displays the name and temperature of a selected city using the OpenWeatherMap API.
- **Custom Page Template**
    - Displays a searchable table of countries, cities, and temperatures with AJAX-based filtering.

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher
- Storefront theme (parent theme)
- OpenWeatherMap API Key (free or paid)

## Installation

1. **Set Up the Storefront Theme**
     - Install and activate the Storefront Theme.
2. **Install the Child Theme**
     - Copy the `storefront-child` directory to the `wp-content/themes/` folder of your WordPress installation.
     - Activate the child theme in the WordPress admin under Appearance > Themes.
3. **API Key Setup**
     - Obtain an API key from OpenWeatherMap.
     - Replace `YOUR_API_KEY` in the code (`functions.php` and AJAX logic) with your OpenWeatherMap API key.
4. **Create Cities and Countries**
     - Add new cities under Cities (custom post type).
     - Assign each city to a country (custom taxonomy).
     - For each city, enter latitude and longitude values in the meta box on the city editor page.

## Usage

1. **Add the Weather Widget**
     - Navigate to Appearance > Widgets.
     - Add the City Weather Widget to any sidebar or widget area.
     - Select a city from the dropdown menu to display its weather information.

2. **Create the Cities Table Page**
     - Create a new page under Pages > Add New.
     - Select the Cities Table template from the “Page Attributes” section.
     - Publish the page and view it to see a searchable table of countries, cities, and their temperatures.

3. **Search for Cities**
     - Use the search bar on the “Cities Table” page to filter cities dynamically with AJAX.

## Development

### File Structure

```
storefront-child/
|-- functions.php              # Contains all theme customizations, CPT, taxonomy, widget, and AJAX logic
|-- style.css                  # Basic styles for the child theme
|-- page-cities-table.php      # Custom page template for the cities table
|-- js/
|   `-- cities-ajax.js         # AJAX logic for the cities search feature
```

### Notes

1. **Security**
     - Nonces are used to secure AJAX requests.
     - Inputs are sanitized before database queries.
2. **Performance**
     - Weather API calls are made dynamically. Consider implementing caching for high-traffic sites.
3. **Customization**
     - You can style the widget and page template further using CSS in the child theme.

## Support

If you encounter any issues, please open a GitHub issue or contact [achmad.azman@gmail.com](mailto:achmad.azman@gmail.com).

## License

This project is open source and available under the [MIT License](LICENSE).

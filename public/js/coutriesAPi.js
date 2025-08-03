// Cache for countries data to avoid multiple API calls
let countriesCache = null;

/**
 * Initialize a country select element with flags
 * @param {Object} config Configuration object
 * @param {string} config.selectId - The ID of the select element
 * @param {string} config.flagId - The ID of the flag container element
 * @param {boolean} [config.defaultToEgypt=true] - Whether to set Egypt as default
 * @param {string} [config.placeholder='Select Country'] - Placeholder text
 * @param {Function} [config.onChange] - Callback function when selection changes
 * @returns {Promise<void>}
 */
async function initializeCountrySelect({
    selectId,
    flagId,
    defaultToEgypt = true,
    placeholder = 'Select Country',
    onChange = null
}) {
    const select = document.getElementById(selectId);
    const flagContainer = document.getElementById(flagId);

    if (!select || !flagContainer) {
        console.error(`Country select initialization failed: Elements not found for ${selectId}`);
        return;
    }

    const updateSelectStyle = (option) => {
        if (option.dataset.flag) {
            option.style.backgroundImage = `url(${option.dataset.flag})`;
            option.style.backgroundRepeat = 'no-repeat';
            option.style.backgroundPosition = '8px center';
            option.style.backgroundSize = '20px auto';
            option.style.paddingLeft = '35px';
        }
    };

    const handleFlagChange = () => {
        const selectedOption = select.options[select.selectedIndex];
        if (selectedOption.value) {
            flagContainer.innerHTML = `<img src="${selectedOption.dataset.flag}" alt="${selectedOption.value} flag">`;
            if (onChange) onChange(selectedOption.value);
        } else {
            flagContainer.innerHTML = '';
            if (onChange) onChange(null);
        }
    };

    try {
        // Use cached data if available
        if (!countriesCache) {
            const response = await fetch('https://restcountries.com/v3.1/all?fields=name,flags');
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            countriesCache = await response.json();
            countriesCache.sort((a, b) => a.name.common.localeCompare(b.name.common));
        }

        // Clear existing options
        select.innerHTML = '';

        // Add placeholder option
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = placeholder;
        select.appendChild(emptyOption);

        let egyptOption;

        // Add country options
        countriesCache.forEach(country => {
            const option = document.createElement('option');
            option.value = country.name.common;
            option.textContent = country.name.common;
            option.dataset.flag = country.flags.svg;
            select.appendChild(option);
            updateSelectStyle(option);

            if (country.name.common === 'Egypt') {
                egyptOption = option;
            }
        });

        // Set Egypt as default if specified
        if (defaultToEgypt && egyptOption) {
            egyptOption.selected = true;
            flagContainer.innerHTML = `<img src="${egyptOption.dataset.flag}" alt="Egypt flag">`;
            if (onChange) onChange('Egypt');
        }

        // Add change event listener
        select.addEventListener('change', handleFlagChange);

    } catch (error) {
        console.error('Error loading countries:', error);
        select.innerHTML = '<option value="">Error loading countries</option>';
        flagContainer.innerHTML = '';
    }
}

// Initialize existing country selects when DOM is loaded
document.addEventListener('DOMContentLoaded', async function() {
    // Example: Initialize existing country selects
    await Promise.all([
        initializeCountrySelect({
            selectId: 'country_of_practices',
            flagId: 'selected-flag'
        }),
        initializeCountrySelect({
            selectId: 'country_of_graduation',
            flagId: 'selected-flag-graduation'
        })
    ]);
});
function showErrorMessage(id, message) {
    const errorElement = document.getElementById(id);
    errorElement.textContent = message;
    errorElement.style.display = 'block';
}

function hideErrorMessage(id) {
    const errorElement = document.getElementById(id);
    errorElement.style.display = 'none';
}

function validateInput(value, id) {
    if (isNaN(value) || value <= 0 || value > 50 || !/^\d+(\.5|0)?$/.test(value)) {
        showErrorMessage(id, 'Kérlek adj meg érvényes méreteket!');
        return false;
    } else {
        hideErrorMessage(id);
        return true;
    }
}

function calculate() {
    const length = parseFloat(document.getElementById('length').value);
    const width = parseFloat(document.getElementById('width').value);
    const rotate = document.getElementById('rotate-checkbox').checked;

    const isLengthValid = validateInput(length, 'length-error');
    const isWidthValid = validateInput(width, 'width-error');

    if (!isLengthValid || !isWidthValid) {
        document.getElementById('diagram').innerHTML = ''; // Clear the diagram if input is invalid
        return;
    }

    const element2x1 = { width: 2, height: 1, area: 2 };
    const element1x1 = { width: 1, height: 1, area: 1 };
    const element0_5x1 = { width: 0.5, height: 1, area: 0.5 };

    // Calculate the number of 2x1 elements that fit
    let num2x1 = Math.floor(length / element2x1.width) * Math.floor(width / element2x1.height);

    // Calculate remaining dimensions after placing 2x1 elements
    let remainingLength = length % 2;
    let remainingWidth = width % 1;
    let num1x1 = 0;
    let num0_5x1 = 0;

    // If both dimensions are even or zero, no 1x1 elements are needed
    if (remainingLength > 0 || remainingWidth > 0) {
        // Calculate the number of 1x1 elements needed
        if (remainingLength >= 1) {
            num1x1 += Math.floor(width);
            remainingLength -= 1;
        }
        if (remainingWidth >= 1) {
            num1x1 += Math.floor(length);
            remainingWidth -= 1;
        }

        // Calculate the number of 0.5x1 elements needed, taking rotation into account
        if (remainingLength >= 0.5) {
            num0_5x1 += Math.floor(width);
        }
        if (remainingWidth >= 0.5) {
            num0_5x1 += Math.floor(length);
        }
    }

    // Ensure correct count of 0.5x1 elements if there are more than needed
    if (remainingLength >= 0.5 && remainingWidth >= 0.5) {
        num0_5x1 -= Math.floor(width);
    }

    let totalArea = length * width;
    let usedArea = num2x1 * element2x1.area + num1x1 * element1x1.area + num0_5x1 * element0_5x1.area;

    // Calculate the number of legs needed
    let legs = 4;
    const rows = Math.ceil(width / element2x1.height);
    const cols = Math.ceil(length / element2x1.width);

    if (rows > 1) {
        legs += (rows - 1) * 2;
    }
    if (cols > 1) {
        legs += (cols - 1) * 2;
    }
    if (rows > 1 && cols > 1) {
        legs += (rows - 1) * (cols - 1);
    }

    const carts = Math.floor(num2x1 / 6);
    const leftover = num2x1 % 6;

    const result = (
      `<p>Színpad területe: ${totalArea} m²</p>` +
      `<p>Szükséges 2x1-es lapok száma: ${num2x1} darab</p>` +
      `<p>Szükséges 1x1-es lapok száma: ${num1x1} darab</p>` +
      `<p>Szükséges 0.5x1-es lapok száma: ${num0_5x1} darab</p>` +
      (leftover == 0 ?
        `<p>Szükséges 2x1-es kocsik száma: ${carts} kocsi</p>` :
        `<p>Szükséges 2x1-es kocsik száma: ${carts} kocsi + ${leftover} darab lap</p>`) +
      `<p><strong>Szükséges lábak száma: ${legs} darab</strong></p>`
    );

    document.getElementById('result').innerHTML = result;

    document.getElementById('diagram').innerHTML = createStageDiagram(length, width, num2x1, num1x1, num0_5x1, rotate);
}

function createStageDiagram(length, width, num2x1, num1x1, num0_5x1, rotate) {
    if (num2x1 <= 0 && num1x1 <= 0 && num0_5x1 <= 0) return ''; // Return empty string if no elements are calculated

    const scale = 20; // Scale factor (1 meter = 20 pixels)
    const svgWidth = length * scale;
    const svgHeight = width * scale;
    let svgContent = `<svg width="${svgWidth}" height="${svgHeight}" xmlns="http://www.w3.org/2000/svg">`;
    
    // Apply rotation if needed
    if (rotate) {
        svgContent = `<svg width="${svgHeight}" height="${svgWidth}" xmlns="http://www.w3.org/2000/svg">
        <g transform="rotate(90) translate(0, -${svgHeight})">`;
    }

    // Draw 2x1 elements
    let x = 0;
    let y = 0;
    for (let i = 0; i < num2x1; i++) {
        svgContent += `<rect x="${x * scale}" y="${y * scale}" width="${2 * scale}" height="${1 * scale}" fill="lightblue" stroke="black" stroke-width="1"/>`;
        x += 2;
        if (x + 2 > length) { // Check if adding another 2x1 element would exceed the length
            x = 0;
            y += 1;
        }
    }

    // Draw 1x1 elements for remaining areas
    x = 0;
    y = 0;
    if (length % 2 !== 0) {
        x = Math.floor(length / 2) * 2; // Position for remaining 1x1 elements on the right
        for (let i = 0; i < Math.floor(width); i++) {
            svgContent += `<rect x="${x * scale}" y="${i * scale}" width="${1 * scale}" height="${1 * scale}" fill="lightgreen" stroke="black" stroke-width="1"/>`;
        }
    }
    if (width % 2 !== 0) {
        y = Math.floor(width / 1); // Position for remaining 1x1 elements at the back
        for (let i = 0; i < Math.floor(length); i++) {
            svgContent += `<rect x="${i * scale}" y="${y * scale}" width="${1 * scale}" height="${1 * scale}" fill="lightgreen" stroke="black" stroke-width="1"/>`;
        }
    }

    // Draw 0.5x1 elements for remaining non-integer dimensions
    if (length % 1 === 0.5) {
        x = Math.floor(length / 2) * 2 + 1; // Adjusted position for remaining 0.5x1 elements
        for (let i = 0; i < Math.floor(width); i++) {
            svgContent += `<rect x="${x * scale}" y="${i * scale}" width="${0.5 * scale}" height="${1 * scale}" fill="lightcoral" stroke="black" stroke-width="1"/>`;
        }
    }
    if (width % 1 === 0.5) {
        y = Math.floor(width / 1); // Position for remaining 0.5x1 elements
        for (let i = 0; i < Math.floor(length); i++) {
            svgContent += `<rect x="${i * scale}" y="${y * scale}" width="${1 * scale}" height="${0.5 * scale}" fill="lightcoral" stroke="black" stroke-width="1"/>`;
        }
    }

    svgContent += '</svg>';
    return svgContent;
}

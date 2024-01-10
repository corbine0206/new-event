<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page</title>
    <?php include'link.php'; ?>
</head>
<body>
    <div class="session">
        <div class="row form-group">
            <div class="col-md-4">
                <label for="technologyLine">Technology Line:</label>
                <input type="text" class="form-control"
                    name="product[1][1][1]"
                    value="1"
                    required
                    data-session-index="1"
                    data-tech-index="1"
                    data-line-index="1">
            </div>
            <div class="col-md-4">
                <!-- Add a Remove button here -->
                <button type="button" class="btn btn-danger"
                    data-remove-index="1"
                    onclick="removeProductAndLine(1, 1, 1)">Remove Product and Technology Line
                </button>
            </div>
        </div>
    </div>
<?php include'script.php'; ?>
<script>
function removeProductAndLine(sessionIndex, techIndex, lineIndex) {
    // Use the data attributes to select the elements
    console.log(`${sessionIndex},${techIndex}, ${lineIndex}` );
    const productElements = document.querySelectorAll(`input[name="product[${sessionIndex}][${techIndex}][]"]
                                                                    [data-session-index="${sessionIndex}"]
                                                                    [data-tech-index="${techIndex}"]
                                                                    [data-line-index="${lineIndex}"]`);

    productElements.forEach((productElement) => {
        if (productElement) {
            productElement.parentElement.parentElement.remove();
        }
    });
    // You can add any additional logic here as needed
}


</script>
</body>
</html>

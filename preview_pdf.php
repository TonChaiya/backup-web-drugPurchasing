<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview PDF</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }
        iframe {
            width: 80%;
            height: 80vh;
            border: none;
        }
        .download-btn {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <h2>Preview ใบเบิก PDF</h2>
    <iframe src="generate_pdf.php?po_number=<?php echo htmlspecialchars($_GET['po_number']); ?>"></iframe>
    <a href="generate_pdf.php?po_number=<?php echo htmlspecialchars($_GET['po_number']); ?>" 
       class="download-btn" target="_blank" download>ดาวน์โหลด PDF</a>
</body>
</html>

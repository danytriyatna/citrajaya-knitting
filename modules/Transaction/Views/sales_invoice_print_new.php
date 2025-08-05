<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Order</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 10px;
            background-color: #eee;
            display: inline-block;
        }
        
        .address {
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .invoice-title {
            text-align: center;
            font-weight: bold;
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .to-section {
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .notes {
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .signature {
            margin-top: 50px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 200px;
            text-align: center;
        }
        
        .signature-placeholder {
            width: 150px;
            height: 80px;
            background-color: #eee;
            margin: 0 auto 10px;
        }
        
        .footer-logo {
            text-align: center;
            margin-top: 30px;
        }
        
        .footer-logo img {
            width: 100px;
            height: auto;
            background-color: #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">[LOGO PLACEHOLDER]</div>
        <div class="address">Jl. Terusan Panyileukan, Kav No. 4, Bandung</div>
    </div>
    <hr style="border: none; border-top: 1px solid #000;" />
    <div class="invoice-title"><span style="border-bottom: 2px solid black; padding-bottom: 2px;">INVOICE ORDER</span></div>
    
    <div class="invoice-info" style="text-align: center;">
        <div><strong>No. 033/LILY/VII/2025</strong></div>
        <div>Date 11 July, 2025</div>
    </div>
    
    <div class="to-section">
        <div><strong>To :</strong> PT. KREASI LILY Jl. Senopati No.8B, Jakarta Selatan Ph : 021-87726451</div>
        <div><strong>Up :</strong> Ibu Ratih</div>
    </div>
    
    <div>Dengan ini pesanan sebagai berikut :</div>
    
    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Deskrpsi</th>
                <th>Warna</th>
                <th>Size S/M</th>
                <th>L/XL</th>
                <th>Harga Unit / Pc (Rp)</th>
                <th>Harga Total /Pc (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td>CARDIGAN STRIPE</td>
                <td>
                    Bw.k + Merah 41-7A (Merah)<br>
                    Bw.k + Biru BCA (Biru)<br>
                    Navy tua + Merah 41-7A (Navy)
                </td>
                <td>300<br>200</td>
                <td>130<br>70<br>150</td>
                <td>95,000<br>95,000<br>95,000</td>
                <td>
                    47,500,000<br>
                    19,000,000<br>
                    33,250,000
                </td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"><strong>Sub Total</strong></td>
                <td>59,750,000</td>
            </tr>
            <tr>
                <td colspan="6"><strong>DP</strong></td>
                <td>59,750,000</td>
            </tr>
            <tr>
                <td colspan="6"><strong>Total</strong></td>
                <td>59,750,000</td>
            </tr>
            <tr>
                <td>Notes</td>
                <td colspan="6"><strong>---</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div>Harga :</div>
    
    <div>Thanks & Regards,</div>
    
    <div class="signature">
        <div class="signature-box">
            <div>CJK Confirmed & Accepted</div>
        </div>
        <div class="signature-box">
            <div class="signature-placeholder"></div>
            <div>Edwin Ferdiansyah</div>
        </div>
    </div>
    
    <div class="footer-logo">
        [FOOTER LOGO PLACEHOLDER]
    </div>
</body>
</html>
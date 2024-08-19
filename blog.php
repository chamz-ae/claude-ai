<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Masak Yuk!</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #f9f9f9;
        }
        header {
            background: linear-gradient(to right, #ff8c00, #ffb74d);
            color: #fff;
            padding: 2rem;
            text-align: center;
            border-bottom: 5px solid #ff6f00;
        }
        header h1 {
            margin: 0;
            font-size: 2.5rem;
        }
        header p {
            margin: 0.5rem 0 0;
            font-size: 1.2rem;
        }
        main {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        article {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            padding: 1.5rem;
        }
        article h2 {
            color: #ff5722;
            font-size: 1.8rem;
            border-bottom: 2px solid #ff5722;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        article ul, article ol {
            margin: 0;
            padding: 0 1.5rem;
        }
        article ul li, article ol li {
            margin-bottom: 0.5rem;
        }
        article ol {
            list-style-type: decimal;
        }
        article ul {
            list-style-type: disc;
        }
        nav {
            text-align: center;
            margin: 2rem 0;
        }
        .pagination {
            display: inline-flex;
            gap: 0.5rem;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .pagination a, .pagination span {
            display: block;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            color: #ff5722;
            border: 1px solid #ff5722;
        }
        .pagination a:hover {
            background-color: #ff5722;
            color: #fff;
        }
        .pagination .active {
            background-color: #ff5722;
            color: #fff;
            cursor: default;
        }
    </style>
</head>
<body>
    <header>
        <h1>Blog Masak Yuk!</h1>
        <p>Temukan resep-resep lezat dan mudah di sini!</p>
    </header>

    <main>
        <!-- Halaman 1 -->
        <div class="page" id="page1">
            <article>
                <h2>1. Nasi Goreng</h2>
                <p>Bahan:</p>
                <ul>
                    <li>2 piring nasi putih</li>
                    <li>2 siung bawang putih, cincang</li>
                    <li>3 siung bawang merah, cincang</li>
                    <li>2 butir telur</li>
                    <li>Kecap manis secukupnya</li>
                    <li>Garam dan merica secukupnya</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Panaskan minyak, tumis bawang putih dan bawang merah hingga harum.</li>
                    <li>Masukkan telur, aduk hingga setengah matang.</li>
                    <li>Tambahkan nasi, kecap manis, garam, dan merica. Aduk rata.</li>
                    <li>Masak hingga nasi panas dan bumbu meresap. Sajikan.</li>
                </ol>
            </article>

            <article>
                <h2>2. Soto Ayam</h2>
                <p>Bahan:</p>
                <ul>
                    <li>500 gr daging ayam</li>
                    <li>2 liter air</li>
                    <li>2 batang serai, memarkan</li>
                    <li>3 lembar daun salam</li>
                    <li>Bumbu halus: bawang merah, bawang putih, kunyit, jahe</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Rebus ayam dengan air hingga mendidih, buang busa yang timbul.</li>
                    <li>Tumis bumbu halus, serai, dan daun salam hingga harum.</li>
                    <li>Masukkan tumisan bumbu ke dalam rebusan ayam, masak hingga ayam empuk.</li>
                    <li>Angkat ayam, suwir-suwir dagingnya.</li>
                    <li>Sajikan kuah soto dengan ayam suwir dan pelengkap lainnya.</li>
                </ol>
            </article>

            <article>
                <h2>3. Rendang Daging</h2>
                <p>Bahan:</p>
                <ul>
                    <li>1 kg daging sapi</li>
                    <li>1 liter santan kental</li>
                    <li>Bumbu halus: bawang merah, bawang putih, cabai, jahe, lengkuas</li>
                    <li>Rempah: serai, daun jeruk, daun salam</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Tumis bumbu halus dan rempah hingga harum.</li>
                    <li>Masukkan daging, aduk rata.</li>
                    <li>Tuang santan, masak dengan api kecil sambil diaduk sesekali.</li>
                    <li>Masak hingga santan menyusut dan daging empuk (sekitar 3-4 jam).</li>
                    <li>Angkat dan sajikan.</li>
                </ol>
            </article>

            <article>
                <h2>4. Sayur Asem</h2>
                <p>Bahan:</p>
                <ul>
                    <li>Sayuran: kacang panjang, labu siam, jagung manis</li>
                    <li>100 gr asam jawa</li>
                    <li>Bumbu: bawang merah, bawang putih, terasi</li>
                    <li>Garam dan gula secukupnya</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Didihkan air, masukkan asam jawa.</li>
                    <li>Tambahkan bumbu yang telah dihaluskan.</li>
                    <li>Masukkan sayuran yang keras terlebih dahulu (jagung, labu siam).</li>
                    <li>Tambahkan kacang panjang, masak hingga semua sayuran matang.</li>
                    <li>Bumbui dengan garam dan gula, koreksi rasa. Sajikan.</li>
                </ol>
            </article>

            <article>
                <h2>5. Gado-gado</h2>
                <p>Bahan:</p>
                <ul>
                    <li>Sayuran rebus: kangkung, bayam, wortel, kacang panjang</li>
                    <li>Pelengkap: tahu goreng, tempe goreng, telur rebus, lontong</li>
                    <li>Bumbu kacang: kacang tanah goreng, cabai, bawang putih, gula merah, air asam jawa</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Haluskan semua bahan bumbu kacang, tambahkan air sedikit demi sedikit.</li>
                    <li>Masak bumbu kacang hingga mengental dan matang.</li>
                    <li>Tata sayuran rebus, tahu, tempe, telur, dan lontong di piring.</li>
                    <li>Siram dengan bumbu kacang. Sajikan.</li>
                </ol>
            </article>
        </div>

        <!-- Halaman 2 -->
        <div class="page" id="page2" style="display: none;">
            <article>
                <h2>6. Ayam Penyet</h2>
                <p>Bahan:</p>
                <ul>
                    <li>4 potong ayam</li>
                    <li>2 siung bawang putih</li>
                    <li>2 siung bawang merah</li>
                    <li>1 buah tomat</li>
                    <li>1 buah cabai merah besar</li>
                    <li>Garam dan merica secukupnya</li>
                    <li>Minyak goreng</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Goreng ayam hingga matang dan kecoklatan.</li>
                    <li>Haluskan bawang putih, bawang merah, tomat, dan cabai merah.</li>
                    <li>Tumis bumbu halus hingga harum.</li>
                    <li>Campurkan ayam goreng dengan bumbu, tambahkan garam dan merica.</li>
                    <li>Sajikan dengan sambal dan lalapan.</li>
                </ol>
            </article>

            <article>
                <h2>7. Pasta Aglio e Olio</h2>
                <p>Bahan:</p>
                <ul>
                    <li>200 gr pasta (spaghetti atau fettuccine)</li>
                    <li>4 siung bawang putih, iris tipis</li>
                    <li>1/4 cangkir minyak zaitun</li>
                    <li>1/4 sendok teh cabai kering</li>
                    <li>Peterseli cincang untuk taburan</li>
                    <li>Garam dan merica secukupnya</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Masak pasta dalam air garam hingga al dente.</li>
                    <li>Tumis bawang putih dengan minyak zaitun hingga keemasan.</li>
                    <li>Tambahkan cabai kering dan pasta yang telah direbus.</li>
                    <li>Aduk rata, beri garam dan merica sesuai selera.</li>
                    <li>Taburi dengan peterseli cincang sebelum disajikan.</li>
                </ol>
            </article>

            <article>
                <h2>8. Tumis Brokoli dan Jamur</h2>
                <p>Bahan:</p>
                <ul>
                    <li>1 kepala brokoli, potong kecil</li>
                    <li>200 gr jamur kancing, iris</li>
                    <li>2 siung bawang putih, cincang</li>
                    <li>2 sendok makan saus tiram</li>
                    <li>1 sendok makan minyak wijen</li>
                    <li>Garam dan merica secukupnya</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Tumis bawang putih dengan minyak wijen hingga harum.</li>
                    <li>Tambahkan brokoli dan jamur, masak hingga brokoli empuk.</li>
                    <li>Tambahkan saus tiram, garam, dan merica. Aduk rata.</li>
                    <li>Sajikan hangat.</li>
                </ol>
            </article>

            <article>
                <h2>9. Ikan Bakar Kecap</h2>
                <p>Bahan:</p>
                <ul>
                    <li>2 ekor ikan kembung, bersihkan</li>
                    <li>3 sendok makan kecap manis</li>
                    <li>2 siung bawang putih, haluskan</li>
                    <li>2 sendok makan air jeruk nipis</li>
                    <li>Garam dan merica secukupnya</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Marinasi ikan dengan kecap manis, bawang putih, air jeruk nipis, garam, dan merica selama 30 menit.</li>
                    <li>Panggang ikan di atas bara api atau grill hingga matang dan kecoklatan.</li>
                    <li>Sajikan dengan sambal dan lalapan.</li>
                </ol>
            </article>

            <article>
                <h2>10. Sop Buntut</h2>
                <p>Bahan:</p>
                <ul>
                    <li>1 kg buntut sapi</li>
                    <li>2 liter air</li>
                    <li>3 siung bawang putih, memarkan</li>
                    <li>2 batang serai, memarkan</li>
                    <li>3 lembar daun salam</li>
                    <li>2 wortel, potong</li>
                    <li>2 kentang, potong</li>
                    <li>Garam dan merica secukupnya</li>
                </ul>
                <p>Cara memasak:</p>
                <ol>
                    <li>Rebus buntut sapi dalam air hingga empuk.</li>
                    <li>Tambahkan bawang putih, serai, daun salam, wortel, dan kentang.</li>
                    <li>Masak hingga semua bahan matang.</li>
                    <li>Bumbui dengan garam dan merica. Sajikan panas.</li>
                </ol>
            </article>
        </div>

        <!-- Pagination -->
        <nav>
            <ul class="pagination">
                <li><a href="#" onclick="showPage(1)">« Prev</a></li>
                <li><a href="#" onclick="showPage(1)">1</a></li>
                <li><a href="#" onclick="showPage(2)">2</a></li>
                <li><a href="#" onclick="showPage(2)">Next »</a></li>
            </ul>
        </nav>
    </main>

    <script>
        function showPage(pageNumber) {
            // Hide all pages
            document.querySelectorAll('.page').forEach(page => page.style.display = 'none');
            // Show the selected page
            document.getElementById('page' + pageNumber).style.display = 'block';
            // Update pagination links
            document.querySelectorAll('.pagination a').forEach(link => link.classList.remove('active'));
            document.querySelector('.pagination a[onclick="showPage(' + pageNumber + ')"]').classList.add('active');
        }

        // Show the first page by default
        showPage(1);
    </script>
</body>
</html>

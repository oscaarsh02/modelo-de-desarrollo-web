<h2>Subir programación académica</h2>

<form action="/procesar-pdf" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="archivo">
    <button type="submit">Subir</button>
</form>
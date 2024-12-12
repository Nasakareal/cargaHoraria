<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-info" style="width: 500px;">
    <div class="p-3">
        <h5>Chat de Ayuda</h5>
        <div id="chatbox" class="card card-info direct-chat direct-chat-primary">
            <div class="card-header">
                <h3 class="card-title">Dalia</h3>
            </div>

            <!-- Contenedor de mensajes -->
            <div class="card-body direct-chat-messages" id="contenedor-mensajes" style="height: 600px; overflow-y: auto;">
                <!-- Los mensajes aparecerán aquí -->
            </div>
            <!-- Campo de entrada -->
            <div class="card-footer">
                <div class="input-group">
                    <input type="text" id="mensaje" placeholder="Escribe tu mensaje aquí" class="form-control" onkeydown="if(event.key === 'Enter') enviarMensaje();">
                    <span class="input-group-append">
                        <button onclick="enviarMensaje()" class="btn btn-primary">Enviar</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</aside>

<!-- /.control-sidebar -->

<!-- Script para el chatbot -->
<script>
function enviarMensaje() {
    let mensaje = document.getElementById("mensaje").value;

    /* Verifica si el campo no está vacío */
    if (mensaje.trim() === "") return;

    /* Convertir a mayúsculas y eliminar acentos */
    mensaje = mensaje
        .toUpperCase() /* Convertir a mayúsculas */
        .normalize("NFD") /* Descomponer caracteres Unicode */
        .replace(/[\u0300-\u036f]/g, "") /* Eliminar los acentos */
        .replace(/[^A-Z0-9 ]/g, ""); /* Eliminar todos los símbolos excepto letras, números y espacios */

    /* Añade el mensaje del usuario al chat */
    const userMessage = `<div class="direct-chat-msg right">
        <div class="direct-chat-infos clearfix">
            <span class="direct-chat-name float-right">Tú</span>
        </div>
        <div class="direct-chat-text bg-primary text-white" style="text-align: right;">
            ${mensaje}
        </div>
    </div>`;
    document.getElementById("contenedor-mensajes").innerHTML += userMessage;

    /* Desplaza el contenedor hacia abajo */
    document.getElementById("contenedor-mensajes").scrollTop = document.getElementById("contenedor-mensajes").scrollHeight;

    /* Enviar mensaje al servidor */
    fetch("<?= APP_URL; ?>/app/helpers/chatbot.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "mensaje=" + encodeURIComponent(mensaje),
    })
    .then(response => response.text())
    .then(data => {
        /* Añade la respuesta del bot al chat de manera procedural */
        let botMessageId = "bot-message-text-" + Date.now();
        let botMessageHTML = `<div class="direct-chat-msg">
            <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-left">
                    <img src="<?= APP_URL; ?>/public/dist/img/Dalia-profile.jpg" alt="Dalia" class="dalia-image"> Dalia
                </span>
            </div>
            <div class="direct-chat-text" id="${botMessageId}"></div>
        </div>`;
        document.getElementById("contenedor-mensajes").innerHTML += botMessageHTML;


        let i = 0;
        function typeMessage() {
            if (i < data.length) {
                document.getElementById(botMessageId).innerHTML += data.charAt(i);
                i++;
                setTimeout(typeMessage, 20);
            } else {
                /* Desplaza el contenedor hacia abajo */
                document.getElementById("contenedor-mensajes").scrollTop = document.getElementById("contenedor-mensajes").scrollHeight;
            }
        }
        typeMessage();
    })
    .catch(error => console.error("Error:", error));

    /* Limpia el campo de entrada */
    document.getElementById("mensaje").value = "";
}
</script>

<style>
.dalia-image {
    width: 40px;
    height: 45px;
    border-radius: 50%;
    margin-right: 10px;
    vertical-align: middle;
}
</style>


<!-- /.control-sidebar -->

<!-- Main Footer -->
<footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
        <span>Versión 1.1.7.4</span> |
        <a href="<?= APP_URL; ?>/portal/reportes/" class="text-decoration-none">
            Informar un Problema
        </a>
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; <?= $ano_actual; ?> <a href="https://ut-morelia.edu.mx/">UTM</a>.</strong> All rights reserved.
</footer>

</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="<?= APP_URL; ?>/public/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?= APP_URL; ?>/public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Datatables -->
<script src="<?= APP_URL; ?>/public/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/jszip/jszip.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?= APP_URL; ?>/public/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>

<!-- AdminLTE App -->
<script src="<?= APP_URL; ?>/public/dist/js/adminlte.min.js"></script>


</body>
</html>



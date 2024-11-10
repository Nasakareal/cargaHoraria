<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-info">
    <div class="p-3">
        <h5>Chat de Ayuda</h5>
        <div id="chatbox" class="card card-info direct-chat direct-chat-primary">
            <div class="card-header">
                <h3 class="card-title">Chat de Ayuda</h3>
            </div>
            <!-- Contenedor de mensajes -->
            <div class="card-body direct-chat-messages" id="contenedor-mensajes" style="height: 400px; overflow-y: auto;">
                <!-- Los mensajes aparecerán aquí -->
            </div>
            <!-- Campo de entrada -->
            <div class="card-footer">
                <div class="input-group">
                    <input type="text" id="mensaje" placeholder="Escribe tu mensaje aquí" class="form-control">
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
    const mensaje = document.getElementById("mensaje").value;

    /* Verifica si el campo no está vacío */
    if (mensaje.trim() === "") return;

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
    fetch("../app/helpers/chatbot.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded",
        },
        body: "mensaje=" + encodeURIComponent(mensaje),
    })
    .then(response => response.text())
    .then(data => {
        /* Añade la respuesta del bot al chat */
        const botMessage = `<div class="direct-chat-msg">
            <div class="direct-chat-infos clearfix">
                <span class="direct-chat-name float-left">Dalia</span>
            </div>
            <div class="direct-chat-text">
                ${data}
            </div>
        </div>`;
        document.getElementById("contenedor-mensajes").innerHTML += botMessage;

        /* Desplaza el contenedor hacia abajo */
        document.getElementById("contenedor-mensajes").scrollTop = document.getElementById("contenedor-mensajes").scrollHeight;
    })
    .catch(error => console.error("Error:", error));

    /* Limpia el campo de entrada */
    document.getElementById("mensaje").value = "";
}


</script>

  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
      Versión 1.0
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; <?=$ano_actual;?> <a href="https://ut-morelia.edu.mx/">UTM</a>.</strong> All rights reserved.
  </footer>
</div>
<!-- ./wrapper -->

<!-- REQUIRED SCRIPTS -->

<!-- jQuery -->
<script src="<?=APP_URL;?>/public/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="<?=APP_URL;?>/public/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- Datatables -->
<script src="<?=APP_URL;?>/public/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-buttons/js/buttons.bootstrap4.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/jszip/jszip.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/pdfmake/pdfmake.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/pdfmake/vfs_fonts.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-buttons/js/buttons.html5.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-buttons/js/buttons.print.min.js"></script>
<script src="<?=APP_URL;?>/public/plugins/datatables-buttons/js/buttons.colVis.min.js"></script>


<!-- AdminLTE App -->
<script src="<?=APP_URL;?>/public/dist/js/adminlte.min.js"></script>
</body>
</html>
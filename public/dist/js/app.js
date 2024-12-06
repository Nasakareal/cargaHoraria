let Titulo = document.title;

window.addEventListener('blur', () => {
    Titulo = document.title;
    document.title = "La niña más bonita";
});

window.addEventListener('focus', () => {
    document.title = Titulo;
});

const canvas = document.getElementById('Flor');
const ctx = canvas.getContext('2d');

console.log("Archivo app.js cargado"); // Verificación de carga

function DibujarPetalo(x, y, RadioX, scala, Rotacion, color, pasos) {
    const AnguloIncrement = (Math.PI / pasos) * 2;
    ctx.save();
    ctx.translate(x, y);
    ctx.rotate(Rotacion);
    ctx.scale(1, scala);
    ctx.beginPath();
    for (let i = 0; i <= pasos; i++) {
        const AnguloActual = i * AnguloIncrement;
        const currentRadius = Math.sin(AnguloActual) * RadioX;
        const PuntoY = Math.sin(AnguloActual) * currentRadius;
        const PuntoX = Math.cos(AnguloActual) * currentRadius;
        if (i === 0) {
            ctx.moveTo(PuntoX, PuntoY);
        } else {
            ctx.lineTo(PuntoX, PuntoY);
        }
    }
    ctx.fillStyle = color;
    ctx.strokeStyle = color;
    ctx.fill();
    ctx.stroke();
    ctx.restore();
}

function DibujarFlor(x, y, NumeroPetalos, RadioXPetalo, RadioYPetalo, AltoTrazo) {
    const PasosTallo = 50;
    const AltoTallo = AltoTrazo / PasosTallo;
    let NuevaY = y;

    const DibujarTallo = () => {
        if (NuevaY < y + AltoTrazo) {
            ctx.beginPath();
            ctx.moveTo(x, y);
            ctx.lineTo(x, NuevaY);
            ctx.lineWidth = 3;
            ctx.strokeStyle = 'black';
            ctx.stroke();
            NuevaY += AltoTallo;
            setTimeout(DibujarTallo, 50);
        } else {
            DibujarHojas(x, y + AltoTrazo - 40, RadioYPetalo);
            DibujarPetalos(x, y, NumeroPetalos, RadioXPetalo);
        }
    };

    DibujarTallo();
}

function DibujarHojas(x, y, RadioYPetalo) {
    DibujarPetalo(x - 20, y, 15, 2, Math.PI / 4, 'green', 50);
    DibujarPetalo(x + 20, y, 15, 2, -Math.PI / 4, 'green', 50);
}

function DibujarPetalos(x, y, NumeroPetalos, RadioXPetalo) {
    const AnguloIncrement = (Math.PI * 2) / NumeroPetalos;
    let contadorPetalos = 0;

    function dibujarSiguientePetalo() {
        if (contadorPetalos < NumeroPetalos) {
            const Angulo = contadorPetalos * AnguloIncrement;
            DibujarPetalo(x, y, RadioXPetalo, 2, Angulo, 'yellow', 100);
            contadorPetalos++;
            setTimeout(dibujarSiguientePetalo, 500);
        } else {
            ctx.beginPath();
            ctx.arc(x, y, 10, 0, Math.PI * 2);
            ctx.fillStyle = 'white';
            ctx.fill();
        }
    }

    dibujarSiguientePetalo();
}

function CrearVarias() {
    canvas.style.display = "block";

    const numFlores = 12;
    const espacioX = canvas.width / 4;
    const espacioY = canvas.height / 3;
    const TamañoFlor = 130;

    let floresDibujadas = 0;

    function dibujarFlorConRetraso() {
        if (floresDibujadas < numFlores) {
            const fila = Math.floor(floresDibujadas / 4);
            const columna = floresDibujadas % 4;
            const x = espacioX * columna + espacioX / 2;
            const y = espacioY * fila + espacioY / 2;

            DibujarFlor(x, y, 8, 30, 80, TamañoFlor);
            floresDibujadas++;
            setTimeout(dibujarFlorConRetraso, 1000);
        } else {
            setTimeout(() => {
                ctx.font = "30px Inclusive Sans";
                ctx.fillStyle = "purple";
                ctx.textAlign = "center";
                ctx.shadowColor = "black";  // Agrega sombra para hacer el texto más visible
                ctx.shadowBlur = 8;
                ctx.fillText("Para la niña más Linda del Mundo.", canvas.width / 2, canvas.height - 30);

                // Restablece la sombra para otros elementos
                ctx.shadowColor = "transparent";
                ctx.shadowBlur = 0;
            }, 1000);
        }
    }

    dibujarFlorConRetraso();
}

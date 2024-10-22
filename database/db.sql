/* 
 Tabla que almacena los diferentes roles de usuario dentro del sistema.
 Cada rol tiene un nombre único y se registran fechas de creación y actualización.
 */

 /* Tabla de roles */
CREATE TABLE roles (
    id_rol INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, /* ID único para cada rol */
    nombre_rol VARCHAR(255) NOT NULL UNIQUE, /* Nombre único del rol */
    fyh_creacion DATETIME NULL, /* Fecha y hora de creación del rol */
    fyh_actualizacion DATETIME NULL, /* Fecha y hora de la última actualización del rol */
    estado VARCHAR(11) /* Estado del rol, por ejemplo 'ACTIVO' o 'INACTIVO' */
) ENGINE=InnoDB;

/*
 Inserta los roles básicos que estarán disponibles en el sistema.
 Cada rol se asocia a un estado y la fecha de creación es la actual.
 */

/* Insertar roles */
INSERT INTO roles (nombre_rol, fyh_creacion, estado) VALUES 
('ADMINISTRADOR', NOW(), '1'), /* Rol de administrador del sistema */
('SUB-DIRECTOR ACADEMICO', NOW(), '1'), /* Rol de sub-director académico */
('ADMINISTRATIVO', NOW(), '1'), /* Rol de personal administrativo */
('SOPORTE', NOW(), '1'), /* Rol para el equipo de soporte técnico */
('OBSERVADOR', NOW(), '1'); /* Rol de observador, con acceso limitado */

/*
 Tabla que almacena información de los usuarios del sistema.
 Cada usuario tiene un rol asignado a través de una clave foránea que referencia la tabla de roles.
 */

/* Tabla de usuarios */
CREATE TABLE usuarios (
    id_usuario INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, /* ID único del usuario */
    nombres VARCHAR(255) NOT NULL, /* Nombre completo del usuario */
    rol_id INT(11) NOT NULL, /* ID del rol que tiene el usuario, referenciado desde la tabla roles */
    email VARCHAR(255) NOT NULL UNIQUE, /* Correo electrónico único del usuario */
    password TEXT NOT NULL, /* Contraseña encriptada del usuario */
    fyh_creacion DATETIME NULL, /* Fecha y hora de creación del usuario */
    fyh_actualizacion DATETIME NULL, /* Fecha y hora de la última actualización de los datos del usuario */
    estado VARCHAR(11), /* Estado del usuario, por ejemplo 'ACTIVO' o 'INACTIVO' */
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol) ON DELETE NO ACTION ON UPDATE CASCADE /* Relación con la tabla roles */
) ENGINE=InnoDB;

/*
 Inserta un usuario administrador en el sistema con el rol de 'ADMINISTRADOR'.
 */

/* Insertar usuarios */
INSERT INTO usuarios (nombres, rol_id, email, password, fyh_creacion, estado) 
VALUES ('Mario Bautista', 1, 'admin@admin.com', 'ansq98', NOW(), '1');

/*
 Tabla que almacena la configuración de las instituciones registradas en el sistema.
 Incluye información como nombre, dirección, teléfono, y logotipo de la institución.
 */

/* Tabla de configuración de instituciones */
CREATE TABLE configuracion_instituciones (
    id_config_institucion INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, /* ID único de la configuración */
    nombre_institucion VARCHAR(255) NOT NULL, /* Nombre de la institución */
    logo VARCHAR(255) NULL,/* URL del logotipo de la institución */
    direccion VARCHAR(255) NOT NULL, /* Dirección física de la institución */
    telefono VARCHAR(100) NULL, /* Número de teléfono de contacto */
    celular VARCHAR(100) NULL, /* Número de teléfono móvil de contacto */
    correo VARCHAR(100) NULL, /* Correo electrónico de contacto */
    fyh_creacion DATETIME NULL, /* Fecha y hora de creación de la configuración */
    fyh_actualizacion DATETIME NULL, /* Fecha y hora de la última actualización de la configuración */
    estado VARCHAR(11) /* Estado de la institución, por ejemplo 'ACTIVO' o 'INACTIVO' */
) ENGINE=InnoDB;

/*
 Inserta los datos de ejemplo para la institución registrada en el sistema.
 */

/* Insertar datos de la institución */
INSERT INTO configuracion_instituciones (nombre_institucion, logo, direccion, telefono, celular, correo, fyh_creacion, estado) 
VALUES ('Universidad Tecnológica de Morelia', 'https://ut-morelia.edu.mx/wp-content/uploads/2022/05/Logo-UTM-Claro.png', 'Av. Vicepresidente Pino Suarez No. 750, Col. Ciudad Industrial, C.P. 58200, Morelia, Michoacán', '4431135900', '524431135900', 'informacion@ut-morelia.edu.mx', '2023-12-28 20:29:10', '1');

/*
 Tabla que almacena los programas académicos ofrecidos por la institución.
 */

/* Tabla de programas */
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY, /* ID único del programa */
    program_name VARCHAR(255) NOT NULL, /* Nombre del programa */
    fyh_creacion DATETIME NULL, /* Fecha y hora de creación del programa */
    fyh_actualizacion DATETIME NULL, /* Fecha y hora de la última actualización del programa */
    estado VARCHAR(11)/* Estado del programa, por ejemplo 'ACTIVO' o 'INACTIVO' */
) ENGINE=InnoDB;

/*
 Inserta los programas académicos disponibles en la institución.
 */

/* Insertar datos de ejemplo en programas */
INSERT INTO programs (program_name, fyh_creacion, estado) VALUES 
('ASESOR FINANCIERO', NOW(), '1'),
('PROCESOS ALIMENTARIOS', NOW(), '1'),
('DISEÑO TEXTIL Y MODA', NOW(), '1'),
('ENERGÍAS RENOVABLES', NOW(), '1'),
('MANTENIMIENTO INDUSTRIAL', NOW(), '1'),
('MECATRÓNICA', NOW(), '1'),
('TECNOLOGÍAS DE LA INFORMACIÓN', NOW(), '1'),
('BIOTECNOLOGÍA', NOW(), '1'),
('GASTRONOMÍA', NOW(), '1'),
('LICENCIATURA DE ENFERMERÍA', NOW(), '1'),
('SISTEMAS AUTOMOTRICES', NOW(), '1'),
('LICENCIATURA EN ECONOMÍA SOCIAL SOLIDARIA', NOW(), '1');

/*
 Tabla que almacena los cuatrimestres dentro de los programas académicos.
 */

/* Tabla de cuatrimestres */
CREATE TABLE terms (
    term_id INT AUTO_INCREMENT PRIMARY KEY,
    term_name VARCHAR(255) NOT NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/*
 Inserta los cuatrimestres disponibles.
 */

/* Insertar datos de ejemplo en cuatrimestres */
INSERT INTO terms (term_name, fyh_creacion, estado) VALUES 
('PRIMERO', NOW(), '1'),
('SEGUNDO', NOW(), '1'),
('TERCERO', NOW(), '1'),
('CUARTO', NOW(), '1'),
('QUINTO', NOW(), '1'),
('SEXTO', NOW(), '1'),
('SEPTIMO', NOW(), '1'),
('OCTAVO', NOW(), '1'),
('NOVENO', NOW(), '1'),
('DECIMO', NOW(), '1'),
('ONCEAVO', NOW(), '1'),
('DOCEAVO', NOW(), '1');

/*
 Tabla que almacena los turnos disponibles para los grupos.
 */

/* Tabla de turnos */
CREATE TABLE shifts (
    shift_id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name ENUM('MATUTINO', 'VESPERTINO', 'MIXTO') NOT NULL,
    schedule_details VARCHAR(255) NOT NULL, /* Detalles sobre los horarios */
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/*
 Inserta los turnos disponibles para los grupos.
 */

/* Insertar turnos */
INSERT INTO shifts (shift_name, schedule_details, fyh_creacion, estado) 
VALUES 
('MATUTINO', 'LUNES A VIERNES de 7:00 A 15:00', NOW(), '1'),
('VESPERTINO', 'LUNES A VIERNES de 12:00 A 20:00', NOW(), '1'),
('MIXTO', 'VIERNES DE 16:00 A 20:00 Y SÁBADO DE 7:00 A 18:00', NOW(), '1');

/*
 Tabla que almacena los grupos que pertenecen a un programa y tienen asignado un turno.
 */

/* Tabla de grupos */
CREATE TABLE `groups` (
    group_id INT AUTO_INCREMENT PRIMARY KEY, /* ID único del grupo */
    group_name VARCHAR(255) NOT NULL, /* Nombre del grupo */
    program_id INT, /* ID del programa al que pertenece el grupo */
    term_id INT, /* ID del cuatrimestre en el que está el grupo */
    year INT, /* Año en el que está activo el grupo */
    volume INT, /* Número de estudiantes en el grupo */
    turn_id INT, /* ID del turno al que pertenece el grupo */
    fyh_creacion DATETIME NULL, /* Fecha y hora de creación del grupo */
    fyh_actualizacion DATETIME NULL, /* Fecha y hora de la última actualización del grupo */
    estado VARCHAR(11), /* Estado del grupo, por ejemplo 'ACTIVO' o 'INACTIVO' */
    FOREIGN KEY (program_id) REFERENCES programs(program_id), /* Relación con la tabla de programas */
    FOREIGN KEY (term_id) REFERENCES terms(term_id), /* Relación con la tabla de cuatrimestres */
    FOREIGN KEY (turn_id) REFERENCES shifts(shift_id) /* Relación con la tabla de turnos */
) ENGINE=InnoDB;


/*
 Inserta grupos de ejemplo, con el programa, periodo, año, volumen y turno correspondiente.
 */

/* Insertar datos de ejemplo en grupos */
INSERT INTO `groups` (group_name, program_id, term_id, year, volume, turn_id, fyh_creacion, estado) VALUES 
('GRUPOA', 3, 1, 2024, 30, 1, NOW(), '1'),
('GRUPOB', 3, 4, 2024, 25, 2, NOW(), '1'),
('GRUPOC', 3, 2, 2024, 20, 3, NOW(), '1'),
('GRUPOD', 1, 3, 2024, 15, 3, NOW(), '1'),
('GRUPOE', 2, 5, 2024, 10, 1, NOW(), '1');


/* Tabla de profesores */
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_name VARCHAR(100) NOT NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    es_local BOOLEAN DEFAULT 1  /* ('local', 'foraneo') */
) ENGINE=InnoDB;

/* Insertar datos de ejemplo en profesores */
INSERT INTO teachers (teacher_name, fyh_creacion, estado, es_local) VALUES 
('PROF. JUAN PÉREZ', NOW(), '1', 1),   /* Profesor local */
('PROF. MARÍA LÓPEZ', NOW(), '1', 0);  /* Profesor foráneo */


/* Tabla de Laboratorios */
CREATE TABLE labs (
    lab_id INT AUTO_INCREMENT PRIMARY KEY,
    lab_name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL
) ENGINE=InnoDB;


INSERT INTO labs (lab_name, description, fyh_creacion) VALUES 
('Laboratorio de Teñido y Estampado', 'Laboratorio para la práctica de teñido y estampado', NOW()),
('Laboratorio de Corte y Confección', 'Laboratorio para aprender corte y confección', NOW());


/* Tabla de materias */
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    is_specialization BOOLEAN DEFAULT 0, /* 0 = No, 1 = Sí */
    hours_consecutive INT, /* Horas que puede impartir consecutivas */
    weekly_hours INT NOT NULL, /* Horas semanales de la materia */
    class_hours INT DEFAULT 0, /* Horas en aula */
    lab_hours INT DEFAULT 0, /* Horas en laboratorio */
    lab_id INT, /* Llave foránea para el laboratorio */
    program_id INT,
    term_id INT,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    CONSTRAINT fk_program FOREIGN KEY (program_id) REFERENCES programs(program_id),
    CONSTRAINT fk_term FOREIGN KEY (term_id) REFERENCES terms(term_id),
    CONSTRAINT fk_lab FOREIGN KEY (lab_id) REFERENCES labs(lab_id) /* Relación con la tabla de laboratorios */
) ENGINE=InnoDB;


INSERT INTO subjects (subject_name, is_specialization, hours_consecutive, weekly_hours, class_hours, lab_hours, lab_id, program_id, term_id, fyh_creacion, estado) VALUES 
('TEXTILES I', 0, 4, 4, 0, 4, 1, 3, 1, NOW(), '1'),
('PRINCIPIOS DE PATRONAJE Y CONFECCIÓN', 0, 6, 6, 0, 6, 2, 3, 1, NOW(), '1'),
('PRINCIPIOS DE DISEÑO DE MODA', 0, 4, 4, 4, 0, NULL, 3, 1, NOW(), '1'),
('INGLÉS I', 0, 5, 5, 5, 0, NULL, 3, 1, NOW(), '1'),
('FUNDAMENTOS MATEMÁTICOS', 0, 7, 7, 7, 0, NULL, 3, 1, NOW(), '1'),
('DESARROLLO HUMANO Y VALORES', 0, 5, 5, 5, 0, NULL, 3, 1, NOW(), '1'),
('COMUNICACIÓN Y HABILIDADES DIGITALES', 0, 7, 7, 7, 0, NULL, 3, 1, NOW(), '1'),
('PATRONAJE INDUSTRIAL', 0, 4, 4, 0, 4, 1, 3, 2, NOW(), '1'),
('INGLÉS II', 0, 6, 6, 0, 6, 2, 3, 2, NOW(), '1'),
('ILUSTRACIÓN DE MODA', 0, 4, 4, 4, 0, NULL, 3, 2, NOW(), '1'),
('HISTORIA Y SOCIOLOGÍA DE LA MODA', 0, 5, 5, 5, 0, NULL, 3, 2, NOW(), '1'),
('HABILIDADES SOCIEMOCIONALES Y MANEJO DE CONFLICTOS', 0, 7, 7, 7, 0, NULL, 3, 2, NOW(), '1'),
('FÍSICA', 0, 5, 5, 5, 0, NULL, 3, 2, NOW(), '1'),
('CONFECCIÓN INDUSTRIAL', 0, 7, 7, 7, 0, NULL, 3, 2, NOW(), '1'),

('MATEMÁTICAS', 0, 3, 5, 5, 0, NULL, 1, 1, NOW(), '1'),
('FÍSICA', 0, 3, 4, 4, 0, NULL, 1, 1, NOW(), '1'),
('PROGRAMACIÓN', 1, 2, 6, 0, 6, 1, 2, 2, NOW(), '1');


/* Tabla de relación profesores y materias */
CREATE TABLE teacher_subjects (
    teacher_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    subject_id INT,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/* Relación entre profesores y materias */
INSERT INTO teacher_subjects (teacher_id, subject_id, fyh_creacion, estado) VALUES 
(1, 1, NOW(), '1'), /* Prof. Juan Pérez con Matemáticas */
(1, 2, NOW(), '1'), /* Prof. Juan Pérez con Física */
(2, 3, NOW(), '1'); /* Prof. María López con Programación */


/* Tabla de salones */
CREATE TABLE classrooms (
    classroom_id INT AUTO_INCREMENT PRIMARY KEY,
    classroom_name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    building VARCHAR(100) NOT NULL, /* Campo para el edificio */
    floor ENUM('ALTA', 'BAJA') NOT NULL, /* Planta que solo puede ser ALTA o BAJA */
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;


/* Insertar datos de ejemplo */
INSERT INTO classrooms (classroom_name, capacity, building, floor, fyh_creacion, fyh_actualizacion, estado) VALUES
('1', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('9', 38, 'EDIFICIO A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('10', 38, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('11', 38, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('12', 38, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('13', 38, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('14', 38, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('15', 30, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 30, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('17', 30, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('M', 38, 'EDIFICIO A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('1', 30, 'EDIFICIO B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 30, 'EDIFICIO B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 30, 'EDIFICIO B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 30, 'EDIFICIO B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 30, 'EDIFICIO B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 30, 'EDIFICIO B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 30, 'EDIFICIO B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('9', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('10', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('11', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('12', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('13', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('14', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('15', 30, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 25, 'EDIFICIO B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('1', 25, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 25, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 25, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 25, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 30, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 30, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 20, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 20, 'EDIFICIO C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('1', 30, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 30, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 40, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 40, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 40, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 40, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 40, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 40, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('9', 40, 'EDIFICIO D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('10', 40, 'EDIFICIO D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('11', 40, 'EDIFICIO D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('12', 40, 'EDIFICIO D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('13', 40, 'EDIFICIO D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('14', 40, 'EDIFICIO D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('15', 40, 'EDIFICIO D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 40, 'EDIFICIO D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('1', 30, 'EDIFICIO E', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('2', 30, 'EDIFICIO E', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('3', 30, 'EDIFICIO E', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 40, 'P1', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('17', 40, 'P1', 'ALTA', NOW(), NOW(), 'ACTIVO');


/* Tabla de horarios */
CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_subject_id INT,
    classroom_id INT,
    schedule_day ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
    start_time TIME,
    end_time TIME,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    group_id INT,
    FOREIGN KEY (teacher_subject_id) REFERENCES teacher_subjects(teacher_subject_id),
    FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id),
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id)
) ENGINE=InnoDB;

INSERT INTO schedules (teacher_subject_id, classroom_id, schedule_day, start_time, end_time, group_id, fyh_creacion, estado) VALUES
(NULL, 1, 'LUNES', '07:00:00', '15:00:00', 1, NOW(), '1'),
(NULL, 2, 'LUNES', '12:00:00', '20:00:00', 1, NOW(), '1'),
(NULL, 1, 'MARTES', '07:00:00', '15:00:00', 1, NOW(), '1'),
(NULL, 2, 'MARTES', '12:00:00', '20:00:00', 1, NOW(), '1'),
(NULL, 1, 'MIERCOLES', '07:00:00', '15:00:00', 1, NOW(), '1'),
(NULL, 2, 'MIERCOLES', '12:00:00', '20:00:00', 1, NOW(), '1'),
(NULL, 1, 'JUEVES', '07:00:00', '15:00:00', 1, NOW(), '1'),
(NULL, 2, 'JUEVES', '12:00:00', '20:00:00', 1, NOW(), '1'),
(NULL, 3, 'SABADO', '7:00:00', '18:00:00', 2, NOW(), '1');


/* Tabla relación de grupos y materias */
CREATE TABLE group_subjects (
    group_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT,
    subject_id INT,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;


/* Tabla relación de programas cuatrimestres y materias */
CREATE TABLE program_term_subjects (
    program_term_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    program_id INT,
    term_id INT,
    subject_id INT,
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (term_id) REFERENCES terms(term_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id)
) ENGINE=InnoDB;

INSERT INTO program_term_subjects (program_id, term_id, subject_id)
VALUES
(3, 1, 1),  /* TEXTILES I */
(3, 1, 2),  /* PRINCIPIOS DE PATRONAJE Y CONFECCIÓN */
(3, 1, 3),  /* PRINCIPIOS DE DISEÑO DE MODA */
(3, 1, 4),  /* INGLÉS I */
(3, 1, 5),  /* FUNDAMENTOS MATEMÁTICOS */
(3, 1, 6),  /* DESARROLLO HUMANO Y VALORES */
(3, 1, 7),  /* COMUNICACIÓN Y HABILIDADES DIGITALES */
(3, 2, 8),  /* PATRONAJE INDUSTRIAL */
(3, 2, 9),  /* INGLÉS II */
(3, 2, 10), /* ILUSTRACIÓN DE MODA */
(3, 2, 11), /* HISTORIA Y SOCIOLOGÍA DE LA MODA */
(3, 2, 12), /* HABILIDADES SOCIEMOCIONALES Y MANEJO DE CONFLICTOS */
(3, 2, 13), /* FÍSICA */
(3, 2, 14), /* CONFECCIÓN INDUSTRIAL */
(1, 1, 15), /* MATEMÁTICAS */
(1, 1, 16), /* FÍSICA */
(2, 2, 17); /* PROGRAMACIÓN */


/* Tabla relación de profesor, programa y cuatrimestre */
CREATE TABLE teacher_program_term (
    teacher_program_term_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    program_id INT,
    term_id INT,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    CONSTRAINT fk_teacher_program FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id),
    CONSTRAINT fk_program_term FOREIGN KEY (program_id) REFERENCES programs(program_id),
    CONSTRAINT fk_term_program FOREIGN KEY (term_id) REFERENCES terms(term_id)
) ENGINE=InnoDB;


/* Tabla de asignación de horarios por materia con ON DELETE CASCADE */
CREATE TABLE schedule_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT,
    subject_id INT,
    teacher_id INT,
    group_id INT,
    classroom_id INT,
    start_time TIME,
    end_time TIME,
    schedule_day ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
    estado VARCHAR(11),
    fyh_creacion DATETIME,
    fyh_actualizacion DATETIME,
    FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id),
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id)
) ENGINE=InnoDB;




 /* Tabla de roles */
CREATE TABLE roles (
    id_rol INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre_rol VARCHAR(255) NOT NULL UNIQUE,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/* Insertar roles */
INSERT INTO roles (nombre_rol, fyh_creacion, estado) VALUES 
('ADMINISTRADOR', NOW(), '1'),
('SUB-DIRECTOR ACADEMICO', NOW(), '1'),
('ADMINISTRATIVO', NOW(), '1'),
('SOPORTE', NOW(), '1'),
('COMISIONES INTERNAS', NOW(), '1'),
('OBSERVADOR', NOW(), '1');

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

CREATE TABLE registro_actividad (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    status ENUM('exitoso', 'fallido') NOT NULL,
    ip VARCHAR(45) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    intentos INT DEFAULT 0,
    intentos_fallidos INT DEFAULT 0,
    bloqueo_activado BOOLEAN DEFAULT FALSE,
    marca_tiempo TIMESTAMP NULL DEFAULT NULL,
    UNIQUE (email)
) ENGINE=InnoDB;

/* Tabla de Permisos */
CREATE TABLE permisos (
    id_permiso INT AUTO_INCREMENT PRIMARY KEY,          /* ID único del permiso */
    nombre_permiso VARCHAR(100) NOT NULL UNIQUE,         /* Nombre único del permiso */
    descripcion VARCHAR(255) NOT NULL,                   /* Descripción del permiso */
    fyh_creacion DATETIME DEFAULT NOW(),                 /* Fecha y hora de creación */
    estado VARCHAR(11) DEFAULT '1'                       /* Estado del permiso, 'ACTIVO' o 'INACTIVO' */
) ENGINE=InnoDB;

INSERT INTO permisos (nombre_permiso, descripcion, fyh_creacion, estado) VALUES
('admin_access', 'ACCESO COMPLETO AL PANEL DE ADMINISTRACIÓN', NOW(), '1'),
('user_manage', 'PERMISO PARA GESTIONAR USUARIOS', NOW(), '1'),
('role_manage', 'PERMISO PARA GESTIONAR ROLES Y PERMISOS DE USUARIOS', NOW(), '1'),
('schedule_manage', 'PERMISO PARA GESTIONAR Y ASIGNAR HORARIOS DE CLASES', NOW(), '1'),
('classroom_assign', 'PERMISO PARA ASIGNAR SALONES A GRUPOS', NOW(), '1'),
('group_view', 'VER GRUPOS Y SU INFORMACIÓN', NOW(), '1'),
('group_edit', 'EDITAR GRUPOS Y SUS ASIGNACIONES', NOW(), '1'),
('subject_view', 'VER LISTA DE MATERIAS', NOW(), '1'),
('subject_edit', 'EDITAR LISTA DE MATERIAS Y SUS ASIGNACIONES', NOW(), '1'),
('teacher_assign', 'ASIGNAR PROFESORES A MATERIAS Y GRUPOS', NOW(), '1');


/* Tabla de Relación Permisos-Usuarios */
CREATE TABLE permisos_roles (
    id_permiso_rol INT AUTO_INCREMENT PRIMARY KEY,
    id_rol INT NOT NULL,
    id_permiso INT NOT NULL,
    fyh_creacion DATETIME DEFAULT NOW(),
    FOREIGN KEY (id_rol) REFERENCES roles(id_rol) ON DELETE CASCADE,
    FOREIGN KEY (id_permiso) REFERENCES permisos(id_permiso) ON DELETE CASCADE
) ENGINE=InnoDB;


/* Permisos para ADMINISTRADOR (id_rol = 1) */
INSERT INTO permisos_roles (id_rol, id_permiso)
SELECT 1, id_permiso FROM permisos;

/* Permisos para SUB-DIRECTOR ACADEMICO (id_rol = 2) */
INSERT INTO permisos_roles (id_rol, id_permiso)
SELECT 2, id_permiso FROM permisos WHERE nombre_permiso IN ('schedule_manage', 'group_view', 'subject_view', 'teacher_assign');

/* Permisos para ADMINISTRATIVO (id_rol = 3) */
INSERT INTO permisos_roles (id_rol, id_permiso)
SELECT 3, id_permiso FROM permisos WHERE nombre_permiso IN ('user_manage', 'group_view', 'subject_view');

/* Permisos para SOPORTE (id_rol = 4) */
INSERT INTO permisos_roles (id_rol, id_permiso)
SELECT 4, id_permiso FROM permisos WHERE nombre_permiso IN ('admin_access', 'user_manage');

/* Permisos para OBSERVADOR (id_rol = 5) */
INSERT INTO permisos_roles (id_rol, id_permiso)
SELECT 5, id_permiso FROM permisos WHERE nombre_permiso IN ('group_view', 'subject_view');

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

/* Insertar datos de la institución */
INSERT INTO configuracion_instituciones (nombre_institucion, logo, direccion, telefono, celular, correo, fyh_creacion, estado) 
VALUES ('Universidad Tecnológica de Morelia', 'https://ut-morelia.edu.mx/wp-content/uploads/2022/05/Logo-UTM-Claro.png', 'Av. Vicepresidente Pino Suarez No. 750, Col. Ciudad Industrial, C.P. 58200, Morelia, Michoacán', '4431135900', '524431135900', 'informacion@ut-morelia.edu.mx', '2023-12-28 20:29:10', '1');

/* Tabla de programas */
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,     /* ID único del programa */
    program_name VARCHAR(255) NOT NULL,            /* Nombre del programa */
    area VARCHAR(255) NOT NULL,                    /* Área del programa */
    fyh_creacion DATETIME NULL,                    /* Fecha y hora de creación del programa */
    fyh_actualizacion DATETIME NULL,               /* Fecha y hora de la última actualización del programa */
    estado VARCHAR(11)                             /* Estado del programa, por ejemplo 'ACTIVO' o 'INACTIVO' */
) ENGINE=InnoDB;

CREATE INDEX idx_area ON programs(area);

/* Insertar datos de ejemplo en programas */
INSERT INTO programs (program_name, area, fyh_creacion, estado) VALUES 
('ASESOR FINANCIERO', 'AFC', NOW(), '1'),
('ASESOR FINANCIERO COOPERATIVO', 'AFC', NOW(), '1'),
('BIOTECNOLOGÍA', 'QMBT', NOW(), '1'),
('DISEÑO Y MODA', 'DMI', NOW(), '1'),
('ENERGÍA Y DESARROLLO SOSTENIBLE AREA ENERGÍA SOLAR', 'ERV', NOW(), '1'),
('ENERGÍA Y DESARROLLO SOSTENIBLE AREA TURBOENERGÍA', 'ERV', NOW(), '1'),
('ENERGÍA Y DESARROLLO SOSTENIBLE AREA TURBO SOLAR', 'ERV', NOW(), '1'),
('ENERGÍAS RENOVABLES', 'ERV', NOW(), '1'),
('ENERGÍAS RENOVABLES AREA CALIDAD Y AHORRO DE ENERGIA', 'ERV', NOW(), '1'),
('ENERGÍAS RENOVABLES ARAE ENERGIA SOLAR', 'ERV', NOW(), '1'),
('ENERGÍAS RENOVABLES AREA TURBOENERGIA', 'ERV', NOW(), '1'),
('GASTRONOMÍA', 'GST', NOW(), '1'),
('INGENIERÍA EN ENTORNOS VIRTUALES Y NEGOCIOS DIGITALES', 'TI', NOW(), '1'),
('LICENCIATURA EN ENFERMERÍA', 'ENF', NOW(), '1'),
('MANTENIMIENTO INDUSTRIAL', 'MAI', NOW(), '1'),
('MECATRÓNICA', 'MEC', NOW(), '1'),
('MTRIA. EN INGENIERÍA APLICADA EN LA INNOVACIÓN TECNOLÓGICA', 'TI', NOW(), '1'),
('TECNOLOGÍAS DE LA INFORMACIÓN', 'TI', NOW(), '1'),
('TECNOLOGÍAS DE LA INFORMACIÓN AREA DESARROLLO DE SOFTWARE MULTIPLATAFORMA', 'TI', NOW(), '1'),
('TECNOLOGÍAS DE LA INFORMACIÓN E INNOVACIÓN DIGITAL', 'TI', NOW(), '1'),
('TECNOLOGÍAS DE LA INFORMACIÓN E INNOVACIÓN DIGITAL AREA ENTORNO VIRTUALES Y NEGOCIOS DIGITALES', 'TI', NOW(), '1'),
('TECNOLOGÍAS DE LA INFORMACIÓN INGENIERÍA EN DESARROLLO Y GESTIÓN DE SOFTWARE', 'TI', NOW(), '1');

/* Tabla de cuatrimestres */
CREATE TABLE terms (
    term_id INT AUTO_INCREMENT PRIMARY KEY,
    term_name VARCHAR(255) NOT NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/* Insertar datos de ejemplo en cuatrimestres */
INSERT INTO terms (term_name, fyh_creacion, estado) VALUES 
('1', NOW(), '1'),
('2', NOW(), '1'),
('3', NOW(), '1'),
('4', NOW(), '1'),
('5', NOW(), '1'),
('6', NOW(), '1'),
('7', NOW(), '1'),
('8', NOW(), '1'),
('9', NOW(), '1'),
('10', NOW(), '1'),
('11', NOW(), '1'),
('12', NOW(), '1'),
('13', NOW(), '1'),
('14', NOW(), '1'),
('15', NOW(), '1'),
('16', NOW(), '1'),
('17', NOW(), '1'),
('18', NOW(), '1'),
('19', NOW(), '1'),
('20', NOW(), '1');


/* Tabla de turnos */
CREATE TABLE shifts (
    shift_id INT AUTO_INCREMENT PRIMARY KEY,
    shift_name ENUM('MATUTINO', 'VESPERTINO', 'MIXTO', 'ZINAPÉCUARO') NOT NULL,
    schedule_details VARCHAR(255) NOT NULL, /* Detalles sobre los horarios */
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;


/* Insertar turnos */
INSERT INTO shifts (shift_name, schedule_details, fyh_creacion, estado) 
VALUES 
('MATUTINO', 'LUNES A VIERNES de 7:00 A 15:00', NOW(), '1'),
('VESPERTINO', 'LUNES A VIERNES de 12:00 A 20:00', NOW(), '1'),
('MIXTO', 'VIERNES DE 16:00 A 20:00 Y SÁBADO DE 7:00 A 18:00', NOW(), '1'),
('ZINAPÉCUARO', 'VIERNES DE 16:00 A 20:00 Y SÁBADO DE 7:00 A 18:00', NOW(), '1');

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

/* Tabla de Laboratorios */
CREATE TABLE labs (
    lab_id INT AUTO_INCREMENT PRIMARY KEY,
    lab_name VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL
) ENGINE=InnoDB;

INSERT INTO labs (lab_name, description, fyh_creacion) VALUES 
('AUTOMATIZACION Y CONTROL', 'ESPACIO PARA SISTEMAS DE AUTOMATIZACIÓN Y CONTROL INDUSTRIAL.', NOW()),
('LABORATORIO DE ALIMENTOS', 'Laboratorio para el procesamiento y análisis de alimentos.', NOW()),
('LABORATORIO DE BEBIDAS', 'ESPACIO PARA LA PREPARACIÓN Y ESTUDIO DE BEBIDAS.', NOW()),
('LABORATORIO DE BIOTECNOLOGIAS PARA PROCESAMIENTO DE ALIMENTOS U079', 'LABORATORIO ESPECIALIZADO EN BIOTECNOLOGÍAS APLICADAS A ALIMENTOS.', NOW()),
('LABORATORIO DE CIENCIAS BASICAS', 'Espacio para prácticas en ciencias fundamentales.', NOW()),
('LABORATORIO DE COCINA CALIENTE 1', 'ÁREA PARA PRÁCTICAS DE COCINA CALIENTE Y ELABORACIÓN  DE PLATOS.', NOW()),
('LABORATORIO DE COCINA CALIENTE 2', 'LABORATORIO ADICIONAL PARA TÉCNICAS AVANZADAS DE COCINA CALIENTE.', NOW()),
('LABORATORIO DE COMPUTO A1', 'LABORATORIO DE COMPUTO EQUIPADO PARA ENSEÑANZA DE INFORMATICA BASICA.', NOW()),
('LABORATORIO DE COMPUTO A2', 'ESPACIO DE COMPUTO AVANZADO PARA APLICACIONES INFORMATICAS.', NOW()),
('LABORATORIO DE COMPUTO B1', 'LABORATORIO DE TECNOLOGÍAS DE LA INFORMACIÓN Y COMPUTO.', NOW()),
('LABORATORIO DE COMPUTO B2', 'ESPACIO PARA DESARROLLO DE SOFTWARE Y APLICACIONES COMPUTACIONALES.', NOW()),
('LABORATORIO DE COMPUTO MULTIDIMENSIONAL', 'ESPACIO DESTINADO A ENTORNOS COMPUTACIONALES MULTIDIMENSIONALES.', NOW()),
('LABORATORIO DE CORTE Y CONFECCIÓN', 'LABORATORIO PARA LA PRÁCTICA DE CORTE Y CONFECCIÓN TEXTIL.', NOW()),
('LABORATORIO DE CULTIVO DE TEJIDOS', 'LABORATORIO PARA CULTIVO DE CELULAS Y TEJIDOS VEGETALES.', NOW()),
('LABORATORIO DE DISEÑO Y PATRONAJE', 'ESPACIO PARA DISEÑO Y DESARROLLO DE PATRONES EN MODA.', NOW()),
('LABORATORIO DE ELECTRICA Y ELECTRÓNICA', 'LABORATORIO DE SISTEMAS ELÉCTRICOS Y ELETRÓNICOS.', NOW()),
('LABORATORIO DE ELECTRICIDAD', 'ESPACIO DE ENSEÑANZA EN FUNDAMENTAS ELÉCTRICOS.', NOW()),
('LABORATORIO DE ELECTRÓNICA ANALÓGICA Y DIGITAL', 'ESPACIO PARA PRÁCTICAS EN ELECTRÓNICA ANALÓGICA Y DIGITAL.', NOW()),
('LABORATORIO DE ENERGIAS RENOVABLES', 'LABORATORIO ENFOCADO EN ENERGÍAS ALTERNATIVAS Y SOSTENIBLES.', NOW()),
('LABORATORIO DE IDIOMAS A', 'LABORATORIO DE IDIOMAS CON RECURSOS MULTIMEDIA.', NOW()),
('LABORATORIO DE IDIOMAS B', 'ESPACIO PARA APRENDIZAJE DE IDIOMAS CON HERRAMIENTAS DIGITALES.', NOW()),
('LABORATORIO DE IDIOMAS B II', 'LABORATORIO AVANZADO PARA PRÁCTICA DE IDIOMAS.', NOW()),
('LABORATORIO DE METAL-MECÁNICA', 'ÁREA PARA PRÁCTICAS DE METALURGIA Y MECANIZADO.', NOW()),
('LABORATORIO DE METROLOGÍA', 'LABORATORIO DE MEDICIÓN Y CALIBRACIÓN DE INSTRUMENTOS.', NOW()),
('LABORATORIO DE MICROBIOLOGÍA', 'ESPACIO PARA ANÁLISIS DE MICROORGANISMOS Y TÉCNICAS MICROBIOLÓGICAS.', NOW()),
('LABORATORIO DE MULTIMEDIA', 'ESPACIO PARA CREACIÓN Y EDICIÓN DE CONTENIDO MULTIMEDIA.', NOW()),
('LABORATORIO DE ÓPTICA', 'LABORATORIO PARA ESTUDIO Y EXPERIMENTACIÓN EN ÓPTICA.', NOW()),
('LABORATORIO DE PANADERIA', 'ESPACIO PARA TÉCNICAS DE PANADERÍA Y RESPOSTERÍA BÁSICA.', NOW()),
('LABORATORIO DE REDES DE CÓMPUTO', 'LABORATORIO PARA REDES DE COMPUTACIÓN Y ADMINISTRACIÓN DE SISTEMAS.', NOW()),
('LABORATORIO DE REPOSTERIA', 'ESPACIO PARA LA ELABORACIÓN DE PRODUCTOS DE REPOSTERÍA AVANZADA.', NOW()),
('LABORATORIO DE ROBOTICA EDUCATIVA, DISEÑO Y MANUFACTURA ADITIVA', 'LABORATORIO PARA ROBÓTICA Y FABRICACIÓN ADITIVA.', NOW()),
('LABORATORIO DE SIMULACIÓN', 'LABORAATORIO PARA SIMULACIÓN EN ENTORNO VIRTUALES.', NOW()),
('LABORATORIO DE TEJIDOS Y BORDADOS', 'LABORATORIO PARA TÉCNICAS DE TEJIDO Y BORDADO.', NOW()),
('LABORATORIO DE TEÑIDO Y ESTAMPADO', 'ESPACIO PARA PRÁCTICAS DE TEÑIDO Y ESTAMPADO EN TEXTILES.', NOW()),
('LABORATORIO DE TERMODINÁMICA', 'LABORATORIO PARA ESTUDIOS EN PRINCIPIOS TERMODINÁMICOS.', NOW()),
('LABORATORIO QUIMICA ANALITICA', 'ESPACIO PARA ANÁLISIS QUÍMICO Y TÉCNICAS DE LABORATORIO.', NOW());

/* Tabla de grupos */
CREATE TABLE `groups` (
    group_id INT AUTO_INCREMENT PRIMARY KEY,       /* ID único del grupo */
    group_name VARCHAR(255) NOT NULL,              /* Nombre del grupo */
    program_id INT,                                /* ID del programa al que pertenece el grupo */
    area VARCHAR(255),                             /* Área del programa al que pertenece el grupo */
    term_id INT,                                   /* ID del cuatrimestre en el que está el grupo */
    volume INT,                                    /* Número de estudiantes en el grupo */
    turn_id INT,                                   /* ID del turno al que pertenece el grupo */
    classroom_assigned INT DEFAULT NULL,           /* ID del salón asignado (puede ser nulo si no está asignado) */
    lab_assigned INT DEFAULT NULL,                 /* ID del laboratorio asignado (puede ser nulo si no está asignado) */
    fyh_creacion DATETIME NULL,                    /* Fecha y hora de creación del grupo */
    fyh_actualizacion DATETIME NULL,               /* Fecha y hora de la última actualización del grupo */
    estado VARCHAR(11),                            /* Estado del grupo, por ejemplo 'ACTIVO' o 'INACTIVO' */
    FOREIGN KEY (program_id) REFERENCES programs(program_id),  /* Relación con la tabla de programas */
    FOREIGN KEY (area) REFERENCES programs(area),              /* Relación con la columna area en programs */
    FOREIGN KEY (term_id) REFERENCES terms(term_id),           /* Relación con la tabla de cuatrimestres */
    FOREIGN KEY (turn_id) REFERENCES shifts(shift_id),         /* Relación con la tabla de turnos */
    FOREIGN KEY (classroom_assigned) REFERENCES classrooms(classroom_id), /* Relación con la tabla de salones */
    FOREIGN KEY (lab_assigned) REFERENCES labs(lab_id)         /* Relación con la tabla de laboratorios */
) ENGINE=InnoDB;


/* Tabla de profesores */
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,       /* ID único del profesor */
    teacher_name VARCHAR(100) NOT NULL,              /* Nombre completo del profesor */
    puesto VARCHAR(100),                             /* Puesto del profesor */
    hours INT DEFAULT 0,                             /* Horas asignadas al profesor para enseñar */
    specialization_area VARCHAR(255),                /* Área de especialización del profesor */
    specialization_program_id INT,                   /* ID del programa de especialización asignado */
    program_id INT,                                  /* ID del programa de adscripción */
    area VARCHAR(255),                               /* Área del programa de adscripción */
    clasificacion VARCHAR(100),                      /* Clasificación del profesor */
    fyh_creacion DATETIME NULL,                      /* Fecha y hora de creación */
    fyh_actualizacion DATETIME NULL,                 /* Fecha y hora de última actualización */
    estado VARCHAR(11),                              /* Estado del profesor, por ejemplo 'ACTIVO' o 'INACTIVO' */
    FOREIGN KEY (program_id) REFERENCES programs(program_id),             /* Relación con el programa de adscripción */
    FOREIGN KEY (specialization_program_id) REFERENCES programs(program_id), /* Relación con el programa de especialización */
    FOREIGN KEY (area) REFERENCES programs(area)                           /* Relación con la columna area en programs */
) ENGINE=InnoDB;

/* Tabla de Materias */
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    weekly_hours INT DEFAULT 0,
    class_hours INT DEFAULT 0,
    lab_hours INT DEFAULT 0,
    lab1_hours INT DEFAULT 0,
    lab2_hours INT DEFAULT 0,
    lab3_hours INT DEFAULT 0,
    max_consecutive_class_hours INT DEFAULT 0,
    max_consecutive_lab_hours INT DEFAULT 0,
    program_id INT,
    term_id INT,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    CONSTRAINT fk_program_subject FOREIGN KEY (program_id) REFERENCES programs(program_id),
    CONSTRAINT fk_term FOREIGN KEY (term_id) REFERENCES terms(term_id)
) ENGINE=InnoDB;


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


/* Insertar datos de ejemplo */
INSERT INTO classrooms (classroom_name, capacity, building, floor, fyh_creacion, fyh_actualizacion, estado) VALUES
('1', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('9', 38, 'EDIFICIO-A', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('10', 38, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('11', 38, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('12', 38, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('13', 38, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('14', 38, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('15', 30, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 30, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('17', 30, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('M', 38, 'EDIFICIO-A', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('1', 30, 'EDIFICIO-B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 30, 'EDIFICIO-B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 30, 'EDIFICIO-B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 30, 'EDIFICIO-B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 30, 'EDIFICIO-B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 30, 'EDIFICIO-B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 30, 'EDIFICIO-B', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('9', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('10', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('11', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('12', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('13', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('14', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('15', 30, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 25, 'EDIFICIO-B', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('1', 25, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 25, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 25, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 25, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 30, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 30, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 20, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 20, 'EDIFICIO-C', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('1', 30, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('2', 30, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('3', 40, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('4', 40, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('5', 40, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('6', 40, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('7', 40, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('8', 40, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('9', 40, 'EDIFICIO-D', 'BAJA', NOW(), NOW(), 'ACTIVO'),
('10', 40, 'EDIFICIO-D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('11', 40, 'EDIFICIO-D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('12', 40, 'EDIFICIO-D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('13', 40, 'EDIFICIO-D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('14', 40, 'EDIFICIO-D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('15', 40, 'EDIFICIO-D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 40, 'EDIFICIO-D', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('1', 30, 'EDIFICIO-E', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('2', 30, 'EDIFICIO-E', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('3', 30, 'EDIFICIO-E', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('16', 40, 'P1', 'ALTA', NOW(), NOW(), 'ACTIVO'),
('17', 40, 'P1', 'ALTA', NOW(), NOW(), 'ACTIVO');


/* Tabla de horarios */
CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,  /* ID único del horario */
    teacher_subject_id INT,                      /* ID de la relación entre profesor y materia */
    classroom_id INT,                            /* ID del salón asignado */
    schedule_day ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),  /* Día de la semana */
    start_time TIME,                             /* Hora de inicio */
    end_time TIME,                               /* Hora de finalización */
    fyh_creacion DATETIME NULL,                  /* Fecha y hora de creación */
    fyh_actualizacion DATETIME NULL,             /* Fecha y hora de actualización */
    estado VARCHAR(11),                          /* Estado del horario */
    group_id INT,                                /* ID del grupo asociado */
    
    /* Claves foráneas con eliminación en cascada */
    FOREIGN KEY (teacher_subject_id) REFERENCES teacher_subjects(teacher_subject_id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE
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
    tipo_espacio VARCHAR(50) DEFAULT 'Aula',
    FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id),
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id)
) ENGINE=InnoDB;


CREATE TABLE educational_levels (
    level_id INT AUTO_INCREMENT PRIMARY KEY,  /* ID único del nivel educativo */
    level_name VARCHAR(255) NOT NULL,         /* Nombre del nivel educativo (TSU, Licenciatura, Maestría) */
    group_id INT,                             /* ID del grupo asociado */
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE /* Relación con eliminación en cascada */
) ENGINE=InnoDB;

INSERT INTO educational_levels (level_name, group_id) VALUES 
('TSU', NULL),          /* Este nivel no está asociado a ningún grupo aún */
('LICENCIATURA', NULL), /* Este nivel no está asociado a ningún grupo aún */
('MAESTRÍA', NULL);     /* Este nivel no está asociado a ningún grupo aún */

/* Tabla de Relación Materia-Laboratorio */
CREATE TABLE subject_labs (
    subject_lab_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT,
    lab_id INT,
    lab_hours INT DEFAULT 0, /* Horas asignadas a este laboratorio específico */
    CONSTRAINT fk_subject FOREIGN KEY (subject_id) REFERENCES subjects(subject_id),
    CONSTRAINT fk_lab FOREIGN KEY (lab_id) REFERENCES labs(lab_id)
) ENGINE=InnoDB;

/* Tabla de relación Grupos-Profesores */
CREATE TABLE teacher_groups (
    teacher_group_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT NOT NULL,  /* ID del profesor */
    group_id INT NOT NULL,    /* ID del grupo */
    fyh_creacion DATETIME DEFAULT NOW(),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE
) ENGINE=InnoDB;


/* Tabla de relación Grupos-Materias */
CREATE TABLE group_subjects (
    group_subject_id INT AUTO_INCREMENT PRIMARY KEY, /* ID único de la relación */
    group_id INT NOT NULL,                           /* ID del grupo */
    subject_id INT NOT NULL,                         /* ID de la materia */
    fyh_creacion DATETIME DEFAULT NOW(),             /* Fecha y hora de creación */
    fyh_actualizacion DATETIME ON UPDATE NOW(),      /* Fecha y hora de última actualización */
    estado VARCHAR(11) DEFAULT '1',                  /* Estado de la relación */
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE ON UPDATE CASCADE, /* Clave foránea a grupos */
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE ON UPDATE CASCADE /* Clave foránea a materias */
) ENGINE=InnoDB;


CREATE TABLE group_schedule_teacher (
    group_schedule_teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,            /* ID del horario de grupo */
    teacher_id INT NOT NULL,             /* ID del profesor asignado */
    subject_id INT NOT NULL,             /* ID de la materia */
    fyh_creacion DATETIME DEFAULT NOW(),
    fyh_actualizacion DATETIME ON UPDATE NOW(),
    estado VARCHAR(11) DEFAULT '1',      /* Estado de la asignación */
    FOREIGN KEY (schedule_id) REFERENCES schedules(schedule_id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE
) ENGINE=InnoDB;




CREATE TABLE building_areas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    building_name VARCHAR(50) NOT NULL,         /* Nombre del edificio */
    area VARCHAR(255) NOT NULL,                 /* Área del programa */
    planta_alta BOOLEAN NOT NULL DEFAULT 0,     /* Indicador de acceso en planta alta */
    planta_baja BOOLEAN NOT NULL DEFAULT 0,     /* Indicador de acceso en planta baja */
    FOREIGN KEY (area) REFERENCES programs(area) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

ALTER TABLE teacher_subjects ADD COLUMN group_id INT AFTER subject_id;
on los grupos

ALTER TABLE teacher_subjects ADD CONSTRAINT fk_group_teacher_subject
FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE ON UPDATE CASCADE;


ALTER TABLE teacher_subjects ADD CONSTRAINT fk_group_teacher_subject
FOREIGN KEY (group_id) REFERENCES `groups`(group_id) ON DELETE CASCADE ON UPDATE CASCADE;


/* Agregar columna planta_alta y planta_baja en classrooms */
ALTER TABLE classrooms
ADD planta_alta BOOLEAN NOT NULL DEFAULT 0 AFTER floor,
ADD planta_baja BOOLEAN NOT NULL DEFAULT 0 AFTER planta_alta;


CREATE TABLE calendario_escolar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_cuatrimestre VARCHAR(100) NOT NULL, -- Ejemplo: "Enero-Abril 2024"
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('ACTIVO', 'INACTIVO') DEFAULT 'ACTIVO',
    fyh_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    fyh_actualizacion DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;


/* Tabla de disponibilidad de horarios de los profesores */
CREATE TABLE teacher_availability (
    id INT AUTO_INCREMENT PRIMARY KEY,                  /* ID único para la disponibilidad */
    teacher_id INT NOT NULL,                            /* ID del profesor (relación con la tabla teachers) */
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
                                                        /* Día de la semana */
    start_time TIME NOT NULL,                           /* Hora de inicio de la disponibilidad */
    end_time TIME NOT NULL,                             /* Hora de fin de la disponibilidad */
    fyh_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,    /* Fecha y hora de creación */
    fyh_actualizacion DATETIME ON UPDATE CURRENT_TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                                        /* Fecha y hora de última actualización */
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE ON UPDATE CASCADE
                                                        /* Relación con la tabla teachers */
) ENGINE=InnoDB;


CREATE TABLE manual_schedule_assignments (
    assignment_id INT(11) NOT NULL AUTO_INCREMENT,
    schedule_id INT(11) DEFAULT NULL,
    subject_id INT(11) DEFAULT NULL,
    teacher_id INT(11) DEFAULT NULL,
    group_id INT(11) DEFAULT NULL,
    classroom_id INT(11) DEFAULT NULL,
    start_time TIME DEFAULT NULL,
    end_time TIME DEFAULT NULL,
    schedule_day ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') DEFAULT NULL,
    estado VARCHAR(11) DEFAULT NULL,
    fyh_creacion DATETIME DEFAULT NULL,
    fyh_actualizacion DATETIME DEFAULT NULL,
    tipo_espacio VARCHAR(50) DEFAULT 'Aula',
    PRIMARY KEY (assignment_id),
    KEY schedule_id (schedule_id),
    KEY subject_id (subject_id),
    KEY teacher_id (teacher_id),
    KEY group_id (group_id),
    KEY classroom_id (classroom_id)
) ENGINE=InnoDB;


ALTER TABLE shifts
MODIFY shift_name ENUM('MATUTINO', 'VESPERTINO', 'MIXTO', 'ZINAPÉCUARO', 'ENFERMERIA', 'MATUTINO AVANZADO', 'VESPERTINO AVANZADO') 
CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci NOT NULL;


INSERT INTO shifts (shift_name, schedule_details, fyh_creacion, estado)
VALUES
('MATUTINO AVANZADO', 'LUNES A VIERNES de 07:00 A 12:00', NOW(), 'ACTIVO'),
('VESPERTINO AVANZADO', 'LUNES A VIERNES de 12:00 A 17:00', NOW(), 'ACTIVO');

UPDATE `groups`
SET turn_id = CASE
    WHEN turn_id = 1 THEN 6
    WHEN turn_id = 2 THEN 7
    ELSE turn_id
END
WHERE term_id >= 7;


UPDATE subjects
SET weekly_hours = CASE
    WHEN subject_id = 1500 THEN 5
    WHEN subject_id = 1501 THEN 7
    WHEN subject_id = 1600 THEN 5
END,
lab_hours = 5,
lab1_hours = 5,
max_consecutive_lab_hours = 5
WHERE subject_id IN (1500, 1501, 1600);


ALTER TABLE schedule_assignments
ADD CONSTRAINT unique_teacher_schedule
UNIQUE (teacher_id, schedule_day, start_time, end_time);


UPDATE subject_labs
SET lab_hours = CASE
    WHEN subject_id = 1500 THEN 5
    WHEN subject_id = 1501 THEN 7
    WHEN subject_id = 1600 THEN 5
END
WHERE subject_id IN (1500, 1501, 1600);


ALTER TABLE manual_schedule_assignments
DROP INDEX schedule_id;


ALTER TABLE manual_schedule_assignments
ADD COLUMN lab1_assigned BOOLEAN DEFAULT 0,
ADD COLUMN lab2_assigned BOOLEAN DEFAULT 0;


ALTER TABLE labs ADD COLUMN area VARCHAR(255) NULL;


ALTER TABLE usuarios
ADD COLUMN area VARCHAR(255) COLLATE utf8mb4_spanish_ci NULL;


ALTER TABLE `groups` DROP FOREIGN KEY groups_ibfk_2;


ALTER TABLE `groups`
ADD CONSTRAINT groups_ibfk_2 FOREIGN KEY (area) REFERENCES programs(area) ON UPDATE CASCADE;


ALTER TABLE teachers DROP FOREIGN KEY teachers_ibfk_3;


ALTER TABLE teachers
ADD CONSTRAINT teachers_ibfk_3 FOREIGN KEY (area) REFERENCES programs(area) ON UPDATE CASCADE;


ALTER TABLE `building_programs` 
ADD COLUMN `fyh_actualizacion` DATETIME NULL DEFAULT NULL 
AFTER `planta_baja`;


ALTER TABLE `building_programs` 
ADD COLUMN `fyh_creacion` DATETIME NULL DEFAULT NULL 
AFTER `planta_baja`;


ALTER TABLE programs MODIFY COLUMN area VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;
ALTER TABLE building_programs MODIFY COLUMN area VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_spanish_ci;


ALTER TABLE schedule_assignments
ADD COLUMN lab_id INT NULL AFTER classroom_id;


ALTER TABLE schedule_assignments
ADD CONSTRAINT fk_lab_id
FOREIGN KEY (lab_id) REFERENCES labs(lab_id)
ON DELETE SET NULL
ON UPDATE CASCADE;


ALTER TABLE subjects
DROP COLUMN class_hours,
DROP COLUMN lab_hours,
DROP COLUMN lab1_hours,
DROP COLUMN lab2_hours,
DROP COLUMN lab3_hours,
DROP COLUMN max_consecutive_lab_hours;


UPDATE `subjects`
SET `max_consecutive_class_hours` = 2;


ALTER TABLE schedule_assignments
DROP INDEX unique_teacher_schedule,
ADD UNIQUE KEY unique_teacher_schedule (teacher_id, schedule_day, start_time, end_time, tipo_espacio);


ALTER TABLE schedule_assignments
ADD CONSTRAINT unique_group_schedule UNIQUE (group_id, schedule_day, start_time);

ALTER TABLE manual_schedule_assignments
ADD CONSTRAINT unique_manual_group_schedule UNIQUE (group_id, schedule_day, start_time);


CREATE TABLE registro_eventos (
    id_evento INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, /* ID único del evento */
    usuario_email VARCHAR(255) NOT NULL, /* Correo del usuario que realiza la acción */
    accion VARCHAR(255) NOT NULL, /* Nombre breve de la acción realizada */
    descripcion TEXT, /* Detalle de la acción */
    fyh_creacion DATETIME DEFAULT CURRENT_TIMESTAMP, /* Fecha y hora de creación del registro */
    ip_usuario VARCHAR(45) NOT NULL, /* Dirección IP del usuario */
    estado VARCHAR(11) DEFAULT 'ACTIVO' /* Estado del registro para controlar si es visible o no */
) ENGINE=InnoDB;



CREATE TABLE schedule_history (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT,
    subject_id INT,
    teacher_id INT,
    group_id INT,
    classroom_id INT,
    lab_id INT,
    start_time TIME,
    end_time TIME,
    schedule_day ENUM('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'),
    estado VARCHAR(11),
    fyh_creacion DATETIME,
    fyh_actualizacion DATETIME,
    tipo_espacio VARCHAR(50),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;



ALTER TABLE schedule_history
ADD COLUMN quarter_name_en VARCHAR(100) NULL AFTER tipo_espacio;













INSERT INTO permisos (nombre_permiso, descripcion, fyh_creacion, estado) VALUES
('lab_block_manage', 'BLOQUEAR Y GESTIONAR LABORATORIOS', NOW(), 1),
('classroom_edit', 'EDITAR INFORMACIÓN DE SALONES', NOW(), 1),
('lab_edit', 'EDITAR INFORMACIÓN DE LABORATORIOS', NOW(), 1),
('group_create', 'CREAR NUEVOS GRUPOS ESCOLARES', NOW(), 1),
('program_edit', 'EDITAR INFORMACIÓN DE PROGRAMAS EDUCATIVOS', NOW(), 1);






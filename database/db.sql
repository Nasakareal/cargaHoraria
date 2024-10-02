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
('ADMINISTRADOR', '2024-09-19 19:10:20', '1'),
('SUB-DIRECTOR ACADEMICO', '2024-09-19 19:10:20', '1'),
('ADMINISTRATIVO', '2024-09-19 19:10:20', '1'),
('SOPORTE', '2024-09-19 19:10:20', '1'),
('OBSERVADOR', '2024-09-19 19:10:20', '1');

/* Tabla de usuarios */
CREATE TABLE usuarios (
    id_usuario INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombres VARCHAR(255) NOT NULL,
    rol_id INT(11) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password TEXT NOT NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    FOREIGN KEY (rol_id) REFERENCES roles(id_rol) ON DELETE NO ACTION ON UPDATE CASCADE
) ENGINE=InnoDB;

/* Insertar usuarios */
INSERT INTO usuarios (nombres, rol_id, email, password, fyh_creacion, estado) 
VALUES ('Mario Bautista', 1, 'admin@admin.com', 'ansq98', '2024-09-19 20:29:10', '1');

/* Tabla de configuración de instituciones */
CREATE TABLE configuracion_instituciones (
    id_config_institucion INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre_institucion VARCHAR(255) NOT NULL,
    logo VARCHAR(255) NULL,
    direccion VARCHAR(255) NOT NULL,
    telefono VARCHAR(100) NULL,
    celular VARCHAR(100) NULL,
    correo VARCHAR(100) NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/* Insertar datos de la institución */
INSERT INTO configuracion_instituciones (nombre_institucion, logo, direccion, telefono, celular, correo, fyh_creacion, estado) 
VALUES ('Universidad Tecnológica de Morelia', 'https://ut-morelia.edu.mx/wp-content/uploads/2022/05/Logo-UTM-Claro.png', 'Av. Vicepresidente Pino Suarez No. 750, Col. Ciudad Industrial, C.P. 58200, Morelia, Michoacán', '4431135900', '524431135900', 'informacion@ut-morelia.edu.mx', '2023-12-28 20:29:10', '1');

/* Tabla de programas */
CREATE TABLE programs (
    program_id INT AUTO_INCREMENT PRIMARY KEY,
    program_name VARCHAR(255) NOT NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/* Insertar datos de ejemplo en programas */
INSERT INTO programs (program_name, fyh_creacion, estado) VALUES 
('ASESOR FINANCIERO', '2024-09-19 20:29:10', '1'),
('PROCESOS ALIMENTARIOS', '2024-09-19 20:29:10', '1'),
('DISEÑO TEXTIL Y MODA', '2024-09-19 20:29:10', '1'),
('ENERGÍAS RENOVABLES', '2024-09-19 20:29:10', '1'),
('MANTENIMIENTO INDUSTRIAL', '2024-09-19 20:29:10', '1'),
('MECATRÓNICA', '2024-09-19 20:29:10', '1'),
('TECNOLOGÍAS DE LA INFORMACIÓN', '2024-09-19 20:29:10', '1'),
('BIOTECNOLOGÍA', '2024-09-19 20:29:10', '1'),
('GASTRONOMÍA', '2024-09-19 20:29:10', '1'),
('LICENCIATURA DE ENFERMERÍA', '2024-09-19 20:29:10', '1'),
('SISTEMAS AUTOMOTRICES', '2024-09-19 20:29:10', '1'),
('LICENCIATURA EN ECONOMÍA SOCIAL SOLIDARIA', '2024-09-19 20:29:10', '1');

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
('PRIMERO', '2024-09-19 20:29:10', '1'),
('SEGUNDO', '2024-09-19 20:29:10', '1');

/* Tabla de grupos */
CREATE TABLE `groups` (
    group_id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(255) NOT NULL,
    program_id INT,
    term_id INT,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    FOREIGN KEY (program_id) REFERENCES programs(program_id),
    FOREIGN KEY (term_id) REFERENCES terms(term_id)
) ENGINE=InnoDB;

/* Insertar datos de ejemplo en grupos */
INSERT INTO `groups` (group_name, program_id, term_id, fyh_creacion, estado) VALUES 
('GRUPOA', 1, 1, '2024-09-19 20:29:10', '1'),
('GRUPOB', 1, 1, '2024-09-19 20:29:10', '1'),
('GRUPOC', 2, 2, '2024-09-19 20:29:10', '1');

/* Tabla de profesores */
CREATE TABLE teachers (
    teacher_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_name VARCHAR(100) NOT NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/* Tabla de materias */
CREATE TABLE subjects (
    subject_id INT AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(100) NOT NULL,
    is_specialization BOOLEAN DEFAULT 0, /* 0 = No, 1 = Sí */
    hours_consecutive INT, /* Horas que puede impartir consecutivas */
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/* Tabla de relación profesores y materias */
CREATE TABLE teacher_subjects (
    teacher_subject_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_id INT,
    subject_id INT,
    weekly_hours INT NOT NULL,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    FOREIGN KEY (teacher_id) REFERENCES teachers(teacher_id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(subject_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

/* Tabla de salones */
CREATE TABLE classrooms (
    classroom_id INT AUTO_INCREMENT PRIMARY KEY,
    classroom_name VARCHAR(50) NOT NULL,
    capacity INT NOT NULL, /* Capacidad del salón */
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11)
) ENGINE=InnoDB;

/* Insertar datos de ejemplo */
INSERT INTO classrooms (classroom_name, capacity, fyh_creacion, fyh_actualizacion, estado) VALUES
('A1', 2090, NOW(), NOW(), 'activo'),
('A2', 1097, NOW(), NOW(), 'activo'),
('A3', 327, NOW(), NOW(), 'activo');


/* Tabla de estudiantes */
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    student_name VARCHAR(100) NOT NULL,
    group_id INT, /* Relación con la tabla de grupos */
    term_id INT,  /* Relación con la tabla de términos */
    program_id INT, /* Relación con la tabla de programas */
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    FOREIGN KEY (group_id) REFERENCES `groups`(group_id),
    FOREIGN KEY (term_id) REFERENCES terms(term_id),
    FOREIGN KEY (program_id) REFERENCES programs(program_id)
) ENGINE=InnoDB;


/* Insertar datos de ejemplo en estudiantes */
INSERT INTO students (student_name, group_id, fyh_creacion, estado) VALUES 
('Juan Pérez', 1, '2024-09-29 10:00:00', '1'),
('María López', 1, '2024-09-29 10:00:00', '1'),
('Carlos Sánchez', 2, '2024-09-29 10:00:00', '1'),
('Ana Rodríguez', 3, '2024-09-29 10:00:00', '1'),
('Luis Gómez', 2, '2024-09-29 10:00:00', '1');


/* Tabla de horarios */
CREATE TABLE schedules (
    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
    teacher_subject_id INT, /* Relaciona al profesor con la materia que imparte */
    classroom_id INT,       /* Opcional, si gestionas salones */
    schedule_day ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'),
    start_time TIME,
    end_time TIME,
    fyh_creacion DATETIME NULL,
    fyh_actualizacion DATETIME NULL,
    estado VARCHAR(11),
    FOREIGN KEY (teacher_subject_id) REFERENCES teacher_subjects(teacher_subject_id),
    FOREIGN KEY (classroom_id) REFERENCES classrooms(classroom_id)
) ENGINE=InnoDB;


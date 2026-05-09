/*
    db.js - Capa de datos local para DualLibro.
    Esta capa simula una base de datos usando localStorage.
    Todos los módulos pueden usar estas funciones para leer y escribir datos.
*/

const DUAL_LIBRO_DB_KEY = 'DualLibroDatabase';

// Datos por defecto cuando la aplicación se inicia por primera vez.
const DEFAULT_DATA = {
    users: {},
    books: [
        { id: 1, titulo: 'Don Quijote de la Mancha', autor: 'Miguel de Cervantes', genero: 'Clásico', contenido: [
            'En un lugar de la Mancha, de cuyo nombre no quiero acordarme, no ha mucho tiempo que vivía un hidalgo...',
            'Tenía en su casa una ama que pasaba de los cuarenta, y una sobrina que no llegaba a los veinte...',
            'Pensó muchos días en las aventuras que leería en sus libros...'
        ] },
        { id: 2, titulo: '1984', autor: 'George Orwell', genero: 'Ficción Distópica', contenido: [
            'Era un día frío y luminoso de abril y los relojes daban las trece...',
            'Winston Smith, con la barbilla metida en el pecho por el frío, se deslizó rápidamente por las puertas...',
            'El Ministerio de la Verdad se alzaba enorme y blanco...'
        ] },
        { id: 3, titulo: 'Cien Años de Soledad', autor: 'Gabriel García Márquez', genero: 'Realismo Mágico', contenido: [
            'Muchos años después, frente al pelotón de fusilamiento, el coronel Aureliano Buendía...',
            'El sol era ardiente y la aldea de Macondo dormía inerme...',
            'Hubo un día en que Melquíades regresó de entre los muertos...'
        ] },
        { id: 4, titulo: 'El Principito', autor: 'Antoine de Saint-Exupéry', genero: 'Infantil', contenido: [
            'Cuando yo tenía seis años vi una vez una lámina extraordinaria en un libro sobre la Selva Virgen...',
            'Me pidieron que dibujara un cordero...',
            'Nunca se está satisfecho donde uno está...'
        ] }
    ],
    works: [],
    session: {
        currentUser: null
    }
};

// Carga el objeto completo de la 'base de datos'.
function loadDatabase() {
    const raw = localStorage.getItem(DUAL_LIBRO_DB_KEY);
    if (!raw) {
        saveDatabase(DEFAULT_DATA);
        return JSON.parse(JSON.stringify(DEFAULT_DATA));
    }
    try {
        return JSON.parse(raw);
    } catch (error) {
        console.error('Error al leer la base de datos local:', error);
        saveDatabase(DEFAULT_DATA);
        return JSON.parse(JSON.stringify(DEFAULT_DATA));
    }
}

// Guarda el objeto completo en localStorage.
function saveDatabase(data) {
    localStorage.setItem(DUAL_LIBRO_DB_KEY, JSON.stringify(data));
}

// Devuelve el usuario por su nombre de usuario.
function getUser(username) {
    const db = loadDatabase();
    return db.users[username] || null;
}

// Agrega un nuevo usuario al sistema.
function addUser(username, password, role) {
    const db = loadDatabase();
    if (db.users[username]) {
        return false; // Ya existe el usuario
    }
    db.users[username] = { password, role };
    saveDatabase(db);
    return true;
}

// Autentica un usuario usando nombre y contraseña.
function authenticateUser(username, password) {
    const user = getUser(username);
    return user && user.password === password ? user : null;
}

// Establece el usuario actual en la sesión.
function setCurrentUser(username) {
    const db = loadDatabase();
    db.session.currentUser = username;
    saveDatabase(db);
}

// Obtiene el usuario actual de la sesión.
function getCurrentUser() {
    const db = loadDatabase();
    return db.session.currentUser;
}

// Limpia la sesión actual.
function clearCurrentUser() {
    const db = loadDatabase();
    db.session.currentUser = null;
    saveDatabase(db);
}

// Devuelve todos los libros del catálogo.
function getBooks() {
    const db = loadDatabase();
    return db.books;
}

// Busca libros por texto en título o autor.
function searchBooks(term) {
    const busqueda = term.trim().toLowerCase();
    if (!busqueda) {
        return getBooks();
    }
    return getBooks().filter(libro =>
        libro.titulo.toLowerCase().includes(busqueda) ||
        libro.autor.toLowerCase().includes(busqueda)
    );
}

// Obtiene un libro por su id.
function getBookById(id) {
    return getBooks().find(libro => libro.id === Number(id)) || null;
}

// Devuelve todas las obras creadas por los escritores.
function getWorks() {
    const db = loadDatabase();
    return db.works;
}

// Devuelve las obras creadas por un escritor específico.
function getWorksByOwner(owner) {
    return getWorks().filter(obra => obra.owner === owner);
}

// Guarda una obra nueva en la base de datos local.
function saveWork(work) {
    const db = loadDatabase();
    const nextId = db.works.length > 0 ? Math.max(...db.works.map(item => item.id)) + 1 : 1;
    db.works.push({ id: nextId, ...work });
    saveDatabase(db);
    return nextId;
}

// Devuelve un id nuevo para un libro o una obra.
function getNextId(collection) {
    return collection.length > 0 ? Math.max(...collection.map(item => item.id)) + 1 : 1;
}

// Inicializa la base de datos cuando la página se carga.
(function initializeDatabase() {
    loadDatabase();
})();

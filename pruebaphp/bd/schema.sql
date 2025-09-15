CREATE TABLE bodegas (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE sucursales (
  id SERIAL PRIMARY KEY,
  bodega_id INTEGER NOT NULL REFERENCES bodegas(id) ON DELETE CASCADE,
  nombre VARCHAR(100) NOT NULL
);

CREATE TABLE monedas (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL
);


CREATE TABLE productos (
  id SERIAL PRIMARY KEY,
  codigo VARCHAR(15) NOT NULL UNIQUE,
  nombre VARCHAR(50) NOT NULL,
  bodega_id INTEGER NOT NULL REFERENCES bodegas(id),
  sucursal_id INTEGER NOT NULL REFERENCES sucursales(id),
  moneda_id INTEGER NOT NULL REFERENCES monedas(id),
  precio NUMERIC(12,2) NOT NULL CHECK (precio > 0),
  descripcion TEXT NOT NULL,
  creado_en TIMESTAMP DEFAULT NOW()
);


CREATE TABLE materiales (
  id SERIAL PRIMARY KEY,
  nombre VARCHAR(50) NOT NULL
);


CREATE TABLE productos_materiales (
  producto_id INTEGER NOT NULL REFERENCES productos(id) ON DELETE CASCADE,
  material_id INTEGER NOT NULL REFERENCES materiales(id),
  PRIMARY KEY (producto_id, material_id)
);


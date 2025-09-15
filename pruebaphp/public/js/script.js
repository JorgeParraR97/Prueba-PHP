document.addEventListener('DOMContentLoaded', function () {
    cargarBodegas();
    cargarMonedas();

    document.getElementById('bodega').addEventListener('change', function () {
        cargarSucursales(this.value);
    });

    document.getElementById('productForm').addEventListener('submit', function (e) {
        e.preventDefault();
        validarYEnviarFormulario();
    });
});

// Cargar select de bodegas
function cargarBodegas() {
    fetch('api/index.php?action=getBodegas')
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            const select = document.getElementById('bodega');
            data.forEach(b => {
                const option = document.createElement('option');
                option.value = b.id;
                option.textContent = b.nombre;
                select.appendChild(option);
            });
        })
        .catch(console.error);
}

// Cargar select de sucursales
function cargarSucursales(bodegaId) {
    const select = document.getElementById('sucursal');
    select.innerHTML = '<option value=""></option>';
    select.disabled = !bodegaId;

    if (!bodegaId) return;

    fetch(`api/index.php?action=getSucursales&bodegaId=${bodegaId}`)
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            data.forEach(s => {
                const option = document.createElement('option');
                option.value = s.id;
                option.textContent = s.nombre;
                select.appendChild(option);
            });
        })
        .catch(console.error);
}

// Cargar select de monedas
function cargarMonedas() {
    fetch('api/index.php?action=getMonedas')
        .then(res => res.json())
        .then(data => {
            if (!Array.isArray(data)) return;
            const select = document.getElementById('moneda');
            data.forEach(m => {
                const option = document.createElement('option');
                option.value = m.id;
                option.textContent = m.nombre;
                select.appendChild(option);
            });
        })
        .catch(console.error);
}

// Validar y enviar formulario
function validarYEnviarFormulario() {
    // Obtener valores
    const codigo = document.getElementById('codigo').value.trim();
    const nombre = document.getElementById('nombre').value.trim();
    const precio = document.getElementById('precio').value.trim();
    const materiales = Array.from(document.querySelectorAll('input[name="materiales[]"]:checked')).map(c => c.value);
    const bodega = document.getElementById('bodega').value;
    const sucursal = document.getElementById('sucursal').value;
    const moneda = document.getElementById('moneda').value;
    const descripcion = document.getElementById('descripcion').value.trim();


    if (!codigo) {
        return alert("El código del producto no puede estar en blanco.");
    }

    if (!/(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]+/.test(codigo)) {
        return alert("El código del producto debe contener letras y números.");
    }

    if (codigo.length < 5 || codigo.length > 15) {
        return alert("El código del producto debe tener entre 5 y 15 caracteres.");
    }
    // 2️⃣ Nombre Producto
    if (!nombre) return alert("El nombre del producto no puede estar en blanco.");
    if (nombre.length < 2 || nombre.length > 50)
        return alert("El nombre del producto debe tener entre 2 y 50 caracteres.");

    // 3️⃣ Precio
    if (!precio) return alert("El precio del producto no puede estar en blanco.");
    if (!/^\d+(\.\d{1,2})?$/.test(precio) || parseFloat(precio) <= 0)
        return alert("El precio del producto debe ser un número positivo con hasta dos decimales.");

    // 4️⃣ Materiales
    if (materiales.length < 2) return alert("Debe seleccionar al menos dos materiales para el producto.");

    // 5️⃣ Bodega
    if (!bodega) return alert("Debe seleccionar una bodega.");

    // 6️⃣ Sucursal
    if (!sucursal) return alert("Debe seleccionar una sucursal para la bodega seleccionada.");

    // 7️⃣ Moneda
    if (!moneda) return alert("Debe seleccionar una moneda para el producto.");

    // 8️⃣ Descripción
    if (!descripcion) return alert("La descripción del producto no puede estar en blanco.");
    if (descripcion.length < 10 || descripcion.length > 1000)
        return alert("La descripción del producto debe tener entre 10 y 1000 caracteres.");

    // Enviar datos
    const formData = new FormData();
    formData.append('codigo', codigo);
    formData.append('nombre', nombre);
    formData.append('bodega_id', bodega);
    formData.append('sucursal_id', sucursal);
    formData.append('moneda_id', moneda);
    formData.append('precio', precio);
    formData.append('descripcion', descripcion);
    materiales.forEach(id => formData.append('materiales[]', id));

    // 🔹 Mostrar los datos en consola
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }

    fetch('api/index.php?action=guardarProducto', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Producto guardado exitosamente!');
                document.getElementById('productForm').reset();
                document.getElementById('sucursal').innerHTML = '<option value="">Seleccione</option>';
                document.getElementById('sucursal').disabled = true;
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Ocurrió un error al guardar el producto.');
        });
}

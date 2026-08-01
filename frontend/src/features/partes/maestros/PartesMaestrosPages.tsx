import { MaestroCrudPage, type MaestroField } from './MaestroCrudPage'

export function AsistentesPage() {
  return (
    <MaestroCrudPage
      title="Asistentes"
      resourcePath="asistentes"
      testIdPrefix="partesMaestrosAsistentes"
      columns={[
        { dataField: 'code', caption: 'Código' },
        { dataField: 'nombre', caption: 'Nombre' },
        { dataField: 'supervisor', caption: 'Supervisor' },
        { dataField: 'activo', caption: 'Activo' },
      ]}
      fields={[
        { key: 'userId', label: 'Usuario', type: 'user' },
        { key: 'code', label: 'Código', type: 'text' },
        { key: 'nombre', label: 'Nombre', type: 'text' },
        { key: 'email', label: 'Email', type: 'text' },
        { key: 'supervisor', label: 'Supervisor', type: 'check' },
        { key: 'activo', label: 'Activo', type: 'check' },
      ]}
      initialForm={{ userId: null, code: '', nombre: '', email: '', supervisor: false, activo: true }}
    />
  )
}

export function ClientesPage() {
  const fields: MaestroField[] = [
    { key: 'code', label: 'Código', type: 'text' },
    { key: 'nombre', label: 'Nombre', type: 'text' },
    {
      key: 'tipoClienteId',
      label: 'Tipo cliente',
      type: 'catalog',
      catalog: 'tipos-cliente',
      displayExpr: (item) =>
        item ? `${String(item.code ?? '')} — ${String(item.descripcion ?? '')}` : '',
    },
    { key: 'userId', label: 'Acceso usuario', type: 'user' },
    { key: 'email', label: 'Email', type: 'text' },
    { key: 'erpCliente', label: 'Erp Cliente', type: 'text', maxLength: 15 },
    { key: 'erpArticulo', label: 'Erp Articulo', type: 'text', maxLength: 15 },
    { key: 'activo', label: 'Activo', type: 'check' },
  ]

  return (
    <MaestroCrudPage
      title="Clientes"
      resourcePath="clientes"
      testIdPrefix="partesMaestrosClientes"
      columns={[
        { dataField: 'code', caption: 'Código' },
        { dataField: 'nombre', caption: 'Nombre' },
        { dataField: 'tipoClienteCode', caption: 'Tipo' },
        { dataField: 'erpCliente', caption: 'Erp Cliente' },
        { dataField: 'erpArticulo', caption: 'Erp Articulo' },
        { dataField: 'activo', caption: 'Activo' },
      ]}
      fields={fields}
      initialForm={{
        code: '',
        nombre: '',
        tipoClienteId: null,
        userId: null,
        email: '',
        erpCliente: '',
        erpArticulo: '',
        activo: true,
      }}
    />
  )
}

export function TiposClientePage() {
  return (
    <MaestroCrudPage
      title="Tipos de cliente"
      resourcePath="tipos-cliente"
      testIdPrefix="partesMaestrosTiposCliente"
      columns={[
        { dataField: 'code', caption: 'Código' },
        { dataField: 'descripcion', caption: 'Descripción' },
        { dataField: 'activo', caption: 'Activo' },
      ]}
      fields={[
        { key: 'code', label: 'Código', type: 'text' },
        { key: 'descripcion', label: 'Descripción', type: 'text' },
        { key: 'activo', label: 'Activo', type: 'check' },
      ]}
      initialForm={{ code: '', descripcion: '', activo: true }}
    />
  )
}

export function TiposTareaPage() {
  return (
    <MaestroCrudPage
      title="Tipos de tarea"
      resourcePath="tipos-tarea"
      testIdPrefix="partesMaestrosTiposTarea"
      columns={[
        { dataField: 'code', caption: 'Código' },
        { dataField: 'descripcion', caption: 'Descripción' },
        { dataField: 'isGenerico', caption: 'Genérico' },
        { dataField: 'isDefault', caption: 'Default' },
        { dataField: 'activo', caption: 'Activo' },
      ]}
      fields={[
        { key: 'code', label: 'Código', type: 'text' },
        { key: 'descripcion', label: 'Descripción', type: 'text' },
        { key: 'isGenerico', label: 'Genérico', type: 'check' },
        { key: 'isDefault', label: 'Default', type: 'check' },
        { key: 'activo', label: 'Activo', type: 'check' },
      ]}
      initialForm={{
        code: '',
        descripcion: '',
        isGenerico: false,
        isDefault: false,
        activo: true,
      }}
    />
  )
}

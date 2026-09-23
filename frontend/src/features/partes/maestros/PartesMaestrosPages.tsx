import { useTranslation } from 'react-i18next'
import { MaestroCrudPage, type MaestroField } from './MaestroCrudPage'

export function AsistentesPage() {
  const { t } = useTranslation()
  return (
    <MaestroCrudPage
      title={t('partes.maestros.asistentes.title')}
      resourcePath="asistentes"
      testIdPrefix="partesMaestrosAsistentes"
      columns={[
        { dataField: 'code', caption: t('partes.informe.field.ejeCodigo') },
        { dataField: 'nombre', caption: t('partes.maestros.field.nombre') },
        { dataField: 'supervisor', caption: t('partes.maestros.field.supervisor') },
        { dataField: 'activo', caption: t('admin.common.activo') },
      ]}
      fields={[
        { key: 'userId', label: t('partes.maestros.field.usuario'), type: 'user' },
        { key: 'code', label: t('partes.informe.field.ejeCodigo'), type: 'text' },
        { key: 'nombre', label: t('partes.maestros.field.nombre'), type: 'text' },
        { key: 'email', label: t('partes.maestros.field.email'), type: 'text' },
        { key: 'supervisor', label: t('partes.maestros.field.supervisor'), type: 'check' },
        { key: 'activo', label: t('admin.common.activo'), type: 'check' },
      ]}
      initialForm={{ userId: null, code: '', nombre: '', email: '', supervisor: false, activo: true }}
    />
  )
}

export function ClientesPage() {
  const { t } = useTranslation()
  const fields: MaestroField[] = [
    { key: 'code', label: t('partes.informe.field.ejeCodigo'), type: 'text' },
    { key: 'nombre', label: t('partes.maestros.field.nombre'), type: 'text' },
    {
      key: 'tipoClienteId',
      label: t('partes.maestros.field.tipoCliente'),
      type: 'catalog',
      catalog: 'tipos-cliente',
      displayExpr: (item) =>
        item ? `${String(item.code ?? '')} — ${String(item.descripcion ?? '')}` : '',
    },
    { key: 'userId', label: t('partes.maestros.field.accesoUsuario'), type: 'user' },
    { key: 'email', label: t('partes.maestros.field.email'), type: 'text' },
    { key: 'erpCliente', label: t('partes.informe.field.erpCliente'), type: 'text', maxLength: 15 },
    { key: 'erpArticulo', label: t('partes.informe.field.erpArticulo'), type: 'text', maxLength: 15 },
    { key: 'activo', label: t('admin.common.activo'), type: 'check' },
  ]

  return (
    <MaestroCrudPage
      title={t('partes.maestros.clientes.title')}
      resourcePath="clientes"
      testIdPrefix="partesMaestrosClientes"
      columns={[
        { dataField: 'code', caption: t('partes.informe.field.ejeCodigo') },
        { dataField: 'nombre', caption: t('partes.maestros.field.nombre') },
        { dataField: 'tipoClienteCode', caption: t('partes.maestros.field.tipo') },
        { dataField: 'erpCliente', caption: t('partes.informe.field.erpCliente') },
        { dataField: 'erpArticulo', caption: t('partes.informe.field.erpArticulo') },
        { dataField: 'activo', caption: t('admin.common.activo') },
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
  const { t } = useTranslation()
  return (
    <MaestroCrudPage
      title={t('partes.maestros.tiposCliente.title')}
      resourcePath="tipos-cliente"
      testIdPrefix="partesMaestrosTiposCliente"
      columns={[
        { dataField: 'code', caption: t('partes.informe.field.ejeCodigo') },
        { dataField: 'descripcion', caption: t('partes.informe.field.ejeDescripcion') },
        { dataField: 'activo', caption: t('admin.common.activo') },
      ]}
      fields={[
        { key: 'code', label: t('partes.informe.field.ejeCodigo'), type: 'text' },
        { key: 'descripcion', label: t('partes.informe.field.ejeDescripcion'), type: 'text' },
        { key: 'activo', label: t('admin.common.activo'), type: 'check' },
      ]}
      initialForm={{ code: '', descripcion: '', activo: true }}
    />
  )
}

export function TiposTareaPage() {
  const { t } = useTranslation()
  return (
    <MaestroCrudPage
      title={t('partes.maestros.tiposTarea.title')}
      resourcePath="tipos-tarea"
      testIdPrefix="partesMaestrosTiposTarea"
      columns={[
        { dataField: 'code', caption: t('partes.informe.field.ejeCodigo') },
        { dataField: 'descripcion', caption: t('partes.informe.field.ejeDescripcion') },
        { dataField: 'isGenerico', caption: t('partes.maestros.field.generico') },
        { dataField: 'isDefault', caption: t('partes.maestros.field.default') },
        { dataField: 'activo', caption: t('admin.common.activo') },
      ]}
      fields={[
        { key: 'code', label: t('partes.informe.field.ejeCodigo'), type: 'text' },
        { key: 'descripcion', label: t('partes.informe.field.ejeDescripcion'), type: 'text' },
        { key: 'isGenerico', label: t('partes.maestros.field.generico'), type: 'check' },
        { key: 'isDefault', label: t('partes.maestros.field.default'), type: 'check' },
        { key: 'activo', label: t('admin.common.activo'), type: 'check' },
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

import { Component, type ErrorInfo, type ReactNode } from 'react'

type ReportDesignerPageErrorBoundaryProps = {
  children: ReactNode
  translate: (key: string) => string
}

type State = { error: Error | null }

/**
 * Si algo lanza durante el montaje del SDK (EmissionReportDesignerPage / DX),
 * sin boundary toda el área principal del shell queda en blanco.
 */
export class ReportDesignerPageErrorBoundary extends Component<
  ReportDesignerPageErrorBoundaryProps,
  State
> {
  public state: State = { error: null }

  public static getDerivedStateFromError(error: unknown): State {
    return { error: error instanceof Error ? error : new Error(String(error)) }
  }

  public componentDidCatch(error: Error, errorInfo: ErrorInfo): void {
    console.error('[ReportDesignerHostPage]', error, errorInfo.componentStack)
  }

  public render(): ReactNode {
    const { error } = this.state
    if (error) {
      const msg = this.props.translate('infra.unexpected')
      return (
        <div
          role="alert"
          data-testid="partesEmisionesDisenadorError"
          style={{ padding: 16, maxWidth: 720 }}
        >
          <p style={{ marginTop: 0, fontWeight: 600 }}>{msg}</p>
          <pre
            style={{
              fontSize: 12,
              opacity: 0.85,
              whiteSpace: 'pre-wrap',
              wordBreak: 'break-word',
            }}
          >
            {error.stack ?? error.message}
          </pre>
        </div>
      )
    }
    return this.props.children
  }
}

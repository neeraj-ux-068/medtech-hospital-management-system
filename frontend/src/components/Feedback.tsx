import { Component } from 'react'
import type { ReactNode } from 'react'
import { Button, Panel } from './ui'

export function LoadingState({ message = 'Loading workspace…' }: { message?: string }) {
  return <Panel aria-busy="true"><p role="status">{message}</p></Panel>
}

export function ErrorState({ message = 'This view could not be displayed.', onRetry }: { message?: string; onRetry?: () => void }) {
  return <Panel><div role="alert"><h2>Something went wrong</h2><p>{message}</p></div>
    {onRetry && <Button onClick={onRetry}>Try again</Button>}
  </Panel>
}

export class PageErrorBoundary extends Component<{ children: ReactNode }, { failed: boolean }> {
  state = { failed: false }
  static getDerivedStateFromError() { return { failed: true } }
  render() {
    return this.state.failed ? <ErrorState onRetry={() => this.setState({ failed: false })} /> : this.props.children
  }
}

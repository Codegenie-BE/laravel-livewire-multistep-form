# Select field behavior

Select fields use their configured `options` as a server-side allow-list. Option keys are normalized to strings because browser form submissions use string values. Non-empty defaults must exist in the configured options. When a select reaches the review step, the human-readable option label is shown instead of the raw option value.

An empty value is reserved for the placeholder / unselected state. Use the optional `placeholder` key to provide custom prompt text.

// types/dashboard.ts

export interface User {
  id: number;
  name: string;
  role?: string;
  secretaria_id?: number;
  unidad_id?: number;
}

export interface Entity {
  key: string;
  label: string;
  fields: Record<string, EntityField>;
}

export interface EntityField {
  type: 'string' | 'number' | 'date' | 'boolean';
  label: string;
}

export interface Widget {
  id: string;
  type: WidgetType;
  title: string;
  entity: string;
  x: number;
  y: number;
  w: number;
  h: number;
  config: WidgetConfig;
}

export type WidgetType = 'bar' | 'line' | 'pie' | 'area' | 'metric' | 'table';

export interface WidgetConfig {
  aggregation: {
    type: 'count' | 'sum' | 'avg' | 'count_distinct';
    field?: string;
  };
  groupBy?: string[];
  filters?: WidgetFilter[];
  limit?: number;
}

export interface WidgetFilter {
  field: string;
  operator: FilterOperator;
  value: any;
}

export type FilterOperator = 
  | 'eq' | 'neq' | 'gt' | 'gte' | 'lt' | 'lte' 
  | 'like' | 'in' | 'between' | 'date_range';

export interface WidgetData {
  success: boolean;
  data: any[];
  entity: string;
  widget_type: WidgetType;
  count: number;
  applied_filters?: {
    role_filters: string;
    user_filters: WidgetFilter[];
  };
  error?: string;
  message?: string;
}

export interface DashboardState {
  widgets: Widget[];
  selectedWidget: string | null;
  draggedEntity: Entity | null;
  entities: Entity[];
  user: User;
}
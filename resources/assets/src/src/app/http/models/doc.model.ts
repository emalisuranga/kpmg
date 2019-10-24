export interface IDocGroup {
  id: string;
  description: string;
  fields: Array<ISubDoc>;
  other_doc_fields: Array<ISubDoc>;
}

export interface ISubDoc {
  id: string;
  name: string;
  key?: string;
  is_required: boolean;
}

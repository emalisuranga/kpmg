export interface IFile {
  file: File;
  companyId: string;
  docId: string;
}

export interface IReqDocument {
  id: number;
  name: string;
  comments: string;
  file_token: string;
}


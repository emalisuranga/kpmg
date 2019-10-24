export interface IUploadDoc {
    name: string;
    file_name_key: string;
    doc_comment: string;
    doc_status: string;
    is_required: boolean;
    file_name: string;
    file_type: string;
    dbid: number;
}

export interface IUploadDocs {
    docs: Array<IUploadDoc>;

 }

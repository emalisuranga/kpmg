export interface IremoveSocietyBulkDoc {
    fileTypeId: number;
    societyId?: number;
    memberId?: number;
}

export interface IgetSocietyBulkList {
    loginUserEmail: string;
}
export interface IgetSocietyRemovePending {
    loginUserEmail: string;
}
export interface IUpdateOptional {
    societyId: string;
    name_si?: string;
    name_ta?: string;
    address_si?: string;
    address_ta?: string;
}

export interface IremoveSociety {
    society_id: number;

}


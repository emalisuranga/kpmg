export interface IDebentureRow {
    id: number;
    showEditPaneForMemb: boolean;
    totalamountsecured: number;
    series: number;
    amount: number;
    description: string;
    nameoftrustees: string;
    dateofcoveringdead: string;
    dateofresolution: string;
    dateofissue: string;
}
  
export interface IDebentureCollection {
    collection: Array<IDebentureRow>;
}

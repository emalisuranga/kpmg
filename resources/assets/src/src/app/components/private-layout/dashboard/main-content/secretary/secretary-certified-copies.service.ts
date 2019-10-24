import { Injectable } from '@angular/core';

@Injectable({
  providedIn: 'root'
})
export class SecretaryCertifiedCopiesService {
  reqID:number;
  secId: number;
  secType: string;
  nic: string;
  fname: string;
  lname: string;
  cnum:string;
  status:string;
  regnum:string;
  name:string;
  constructor() { }

    // for continue upload process after some reasons...
    setReqID(reqID: number) {
      this.reqID = reqID;
    }
  
    get getReqID() {
      return this.reqID;
    }

    setStatus(status: string) {
      this.status = status;
    }
  
    get getStatus() {
      return this.status;
    }

    setSecId(secId: number) {
      this.secId = secId;
    }
  
    get getSecId() {
      return this.secId;
    }
  
    setSecType(secType: string) {
      this.secType = secType;
    }
  
    get getSecType() {
      return this.secType;
    }
  
    setNic(nic: string) {
      this.nic = nic;
    }
  
    get getNic() {
      return this.nic;
    }

    setFname(fname: string) {
      this.fname = fname;
    }
  
    get getFname() {
      return this.fname;
    }

    setLname(lname: string) {
      this.lname = lname;
    }
  
    get getLname() {
      return this.lname;
    }

    setCnum(cnum: string) {
      this.cnum = cnum;
    }
  
    get getCnum() {
      return this.cnum;
    }

    setRnum(regnum: string) {
      this.regnum = regnum;
    }
  
    get getRnum() {
      return this.regnum;
    }

    setName(name: string) {
      this.name = name;
    }
  
    get getName() {
      return this.name;
    }
}

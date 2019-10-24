import { map } from 'rxjs/operators';
import { Observable } from 'rxjs';
import { Directive } from '@angular/core';
import { NG_ASYNC_VALIDATORS, AsyncValidator, AbstractControl, ValidationErrors } from '@angular/forms';
import { UserService } from '../../http/services/user.service';

const newLocal = '[uniqueEmail]';
@Directive({
  selector: newLocal,
  providers: [{provide: NG_ASYNC_VALIDATORS, useExisting: UniqueEmailDirective, multi: true}]
})
export class UniqueEmailDirective implements AsyncValidator {


  constructor(private User: UserService) { }

  validate(c: AbstractControl): Promise<ValidationErrors> | Observable<ValidationErrors> {
    return this.User.getUserByEmail(c.value).pipe(
      map(users => {
        return users != null ? { 'uniqueEmail' : true } : null;
      })
    );
  }
}

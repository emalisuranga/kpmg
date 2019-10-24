import { async, ComponentFixture, TestBed } from '@angular/core/testing';

import { DirectorModelComponent } from './director-model.component';

describe('DirectorModelComponent', () => {
  let component: DirectorModelComponent;
  let fixture: ComponentFixture<DirectorModelComponent>;

  beforeEach(async(() => {
    TestBed.configureTestingModule({
      declarations: [ DirectorModelComponent ]
    })
    .compileComponents();
  }));

  beforeEach(() => {
    fixture = TestBed.createComponent(DirectorModelComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});

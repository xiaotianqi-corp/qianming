export default function IdentityForm({ requirements }) {
    const { data, setData, post, processing } = useForm({
      first_name: '',
      last_name: '',
      document_type: '',
      document_number: '',
      email: '',
      phone: '',
      documents: {}
    });
  
    return (
      <form onSubmit={e => {
        e.preventDefault();
        post(route('signatures.identity.store'));
      }}>
        <TextInput label="Nombres" />
        <TextInput label="Apellidos" />
  
        {requirements.requiresSelfie && (
          <FileInput label="Selfie" />
        )}
  
        <SubmitButton loading={processing} />
      </form>
    );
  }
  